import React, { useEffect, useState } from 'react';
import { Carte, EnteteCarte, TitreCarte, SousTitreCarte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { Alerte } from '../../components/ui/Alerte';
import api from '../../lib/api';
import { Utilisateur } from '../../types';
import { formaterDate } from '../../lib/utils';
import { useAuthStore } from '../../stores/authStore';
import { Users, UserCheck, UserX, Trash2, Shield, Phone, Mail } from 'lucide-react';

export const GestionMembres: React.FC = () => {
  const { utilisateur: currentUser } = useAuthStore();
  const [utilisateurs, setUtilisateurs] = useState<Utilisateur[]>([]);
  const [chargement, setChargement] = useState(true);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  const chargerUtilisateurs = async () => {
    setChargement(true);
    try {
      const rep = await api.get<{ succes: boolean; utilisateurs: Utilisateur[] }>('/utilisateurs');
      if (rep.data.succes) setUtilisateurs(rep.data.utilisateurs);
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerUtilisateurs();
  }, []);

  const basculerStatut = async (id: number) => {
    try {
      const rep = await api.patch(`/utilisateurs/${id}/toggle`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerUtilisateurs();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors du changement de statut.' });
    }
  };

  const supprimerUtilisateur = async (id: number, nom: string) => {
    if (!confirm(`Voulez-vous vraiment supprimer le compte de ${nom} ?`)) return;
    try {
      const rep = await api.delete(`/utilisateurs/${id}`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerUtilisateurs();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Impossible de supprimer.' });
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
          Gestion des Membres & Inscriptions
        </h2>
        <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Validation des nouvelles adhésions (Sas d'entrée) et gestion des rôles
        </p>
      </div>

      {message && (
        <Alerte variante={message.type === 'succes' ? 'succes' : 'danger'}>
          {message.texte}
        </Alerte>
      )}

      <Carte>
        <div className="overflow-x-auto">
          {utilisateurs.length === 0 ? (
            <div className="text-center py-10 text-xs text-slate-400">
              Aucun utilisateur répertorié.
            </div>
          ) : (
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-slate-100 dark:border-sombre-bordure text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                  <th className="pb-3 px-3">Membre</th>
                  <th className="pb-3 px-3">Coordonnées</th>
                  <th className="pb-3 px-3">Rôle</th>
                  <th className="pb-3 px-3">Accès Compte</th>
                  <th className="pb-3 px-3">Inscrit le</th>
                  <th className="pb-3 px-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure">
                {utilisateurs.map((u) => {
                  const estMoi = u.id === currentUser?.id;

                  return (
                    <tr key={u.id} className="hover:bg-slate-50/50 dark:hover:bg-sombre-carte/50 transition-colors">
                      <td className="py-3.5 px-3">
                        <div className="flex items-center gap-2.5">
                          <div className="w-8 h-8 rounded-xl bg-primaire-100 dark:bg-primaire-950/60 text-primaire-700 dark:text-primaire-400 font-bold flex items-center justify-center text-xs">
                            {u.name.charAt(0).toUpperCase()}
                          </div>
                          <div>
                            <span className="font-bold text-slate-900 dark:text-white block">{u.name}</span>
                            {estMoi && (
                              <span className="text-[10px] text-primaire-600 dark:text-primaire-400 font-semibold">
                                (Vous-même)
                              </span>
                            )}
                          </div>
                        </div>
                      </td>
                      <td className="py-3.5 px-3 space-y-0.5 text-slate-500 dark:text-slate-400">
                        <div className="flex items-center gap-1.5">
                          <Mail className="w-3.5 h-3.5" />
                          <span>{u.email}</span>
                        </div>
                        <div className="flex items-center gap-1.5">
                          <Phone className="w-3.5 h-3.5" />
                          <span>{u.phone}</span>
                        </div>
                      </td>
                      <td className="py-3.5 px-3 capitalize font-semibold text-slate-700 dark:text-slate-300">
                        {u.role}
                      </td>
                      <td className="py-3.5 px-3">
                        <Badge variante={u.is_active ? 'succes' : 'alerte'}>
                          {u.is_active ? 'Actif' : 'En attente validation'}
                        </Badge>
                      </td>
                      <td className="py-3.5 px-3 text-slate-400">
                        {formaterDate(u.created_at)}
                      </td>
                      <td className="py-3.5 px-3 text-right space-x-2 whitespace-nowrap">
                        {!estMoi && (
                          <Bouton
                            variante={u.is_active ? 'contour' : 'primaire'}
                            taille="sm"
                            onClick={() => basculerStatut(u.id)}
                            icone={u.is_active ? <UserX className="w-3.5 h-3.5 mr-1" /> : <UserCheck className="w-3.5 h-3.5 mr-1" />}
                          >
                            {u.is_active ? 'Désactiver' : 'Activer'}
                          </Bouton>
                        )}

                        {!estMoi && (
                          <Bouton
                            variante="contour"
                            taille="sm"
                            onClick={() => supprimerUtilisateur(u.id, u.name)}
                            icone={<Trash2 className="w-3.5 h-3.5 text-rose-500" />}
                          />
                        )}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          )}
        </div>
      </Carte>
    </div>
  );
};
