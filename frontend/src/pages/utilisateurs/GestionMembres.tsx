import React, { useEffect, useState, useMemo } from 'react';
import { Carte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { Alerte } from '../../components/ui/Alerte';
import { BarreFiltresTableau, OptionFiltre } from '../../components/ui/BarreFiltresTableau';
import { SqueletteTableau } from '../../components/ui/SqueletteChargement';
import api from '../../lib/api';
import { Utilisateur } from '../../types';
import { formaterDate } from '../../lib/utils';
import { useAuthStore } from '../../stores/authStore';
import { UserCheck, UserX, Trash2, Shield, Phone, Mail, UserPlus } from 'lucide-react';

export const GestionMembres: React.FC = () => {
  const { utilisateur: currentUser } = useAuthStore();
  const [utilisateurs, setUtilisateurs] = useState<Utilisateur[]>([]);
  const [chargement, setChargement] = useState(true);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  // États pour les filtres et la recherche
  const [recherche, setRecherche] = useState('');
  const [filtreStatut, setFiltreStatut] = useState<string>('tous');
  const [filtreRole, setFiltreRole] = useState<string>('tous');

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

  // Filtrage combiné (Recherche textuelle + Statut + Rôle)
  const utilisateursFiltres = useMemo(() => {
    return utilisateurs.filter((u) => {
      // 1. Recherche par nom, email, téléphone
      const matchRecherche =
        !recherche ||
        u.name.toLowerCase().includes(recherche.toLowerCase()) ||
        u.email.toLowerCase().includes(recherche.toLowerCase()) ||
        (u.phone && u.phone.includes(recherche));

      // 2. Filtre par statut (actif / inactif)
      const matchStatut =
        filtreStatut === 'tous' ||
        (filtreStatut === 'actif' && u.is_active) ||
        (filtreStatut === 'inactif' && !u.is_active);

      // 3. Filtre par rôle
      const matchRole =
        filtreRole === 'tous' || u.role.toLowerCase() === filtreRole.toLowerCase();

      return matchRecherche && matchStatut && matchRole;
    });
  }, [utilisateurs, recherche, filtreStatut, filtreRole]);

  // Options pour la barre de filtres
  const optionsFiltresStatut: OptionFiltre[] = [
    { cle: 'tous', label: 'Tous les statuts', compte: utilisateurs.length },
    { cle: 'actif', label: 'Actifs', compte: utilisateurs.filter((u) => u.is_active).length },
    { cle: 'inactif', label: 'En attente', compte: utilisateurs.filter((u) => !u.is_active).length },
  ];

  return (
    <div className="space-y-6">
      {/* En-tête de page */}
      <div>
        <h2 className="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
          Gestion des Membres & Inscriptions
        </h2>
        <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Validation des nouvelles adhésions (Sas d'entrée), gestion des accès et attribution des rôles
        </p>
      </div>

      {message && (
        <Alerte variante={message.type === 'succes' ? 'succes' : 'danger'}>
          {message.texte}
        </Alerte>
      )}

      {/* Barre de recherche et de filtres responsive */}
      <BarreFiltresTableau
        termeRecherche={recherche}
        surChangementRecherche={setRecherche}
        placeholder="Rechercher par nom, email, téléphone..."
        optionsFiltres={optionsFiltresStatut}
        filtreActif={filtreStatut}
        surChangementFiltre={setFiltreStatut}
        totalResultats={utilisateursFiltres.length}
        libelleResultats="membre(s) listé(s)"
      />

      {/* Carte du Tableau */}
      <Carte className="p-0 overflow-hidden border border-slate-200/80 dark:border-sombre-bordure">
        {chargement ? (
          <div className="p-6">
            <SqueletteTableau lignes={6} />
          </div>
        ) : (
          <div className="overflow-x-auto">
            {utilisateursFiltres.length === 0 ? (
              <div className="text-center py-12 text-xs text-slate-400 dark:text-slate-500">
                Aucun membre ne correspond à vos critères de recherche.
              </div>
            ) : (
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-100 dark:border-sombre-bordure/60 text-slate-400 dark:text-slate-500 font-semibold tracking-wider uppercase text-[11px] bg-slate-50/50 dark:bg-sombre-surface/50">
                    <th className="py-3.5 px-4 sm:px-6">Membre</th>
                    <th className="py-3.5 px-4">Coordonnées</th>
                    <th className="py-3.5 px-4">Rôle</th>
                    <th className="py-3.5 px-4">Accès Compte</th>
                    <th className="py-3.5 px-4">Inscrit le</th>
                    <th className="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure/40">
                  {utilisateursFiltres.map((u) => {
                    const estMoi = u.id === currentUser?.id;

                    return (
                      <tr
                        key={u.id}
                        className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/40 transition-colors"
                      >
                        {/* Membre avec médaillon d'avatar soigné */}
                        <td className="py-4 px-4 sm:px-6">
                          <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700/80 font-bold flex items-center justify-center text-xs shrink-0 shadow-2xs">
                              {u.name.charAt(0).toUpperCase()}
                            </div>
                            <div className="min-w-0">
                              <span className="font-bold text-slate-900 dark:text-white block truncate">
                                {u.name}
                              </span>
                              {estMoi && (
                                <span className="text-[10px] text-blue-600 dark:text-blue-400 font-semibold">
                                  (Vous-même)
                                </span>
                              )}
                            </div>
                          </div>
                        </td>

                        {/* Coordonnées */}
                        <td className="py-4 px-4 space-y-0.5 text-slate-500 dark:text-slate-400">
                          <div className="flex items-center gap-1.5">
                            <Mail className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                            <span className="truncate max-w-[160px] sm:max-w-none">{u.email}</span>
                          </div>
                          {u.phone && (
                            <div className="flex items-center gap-1.5 text-[11px]">
                              <Phone className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                              <span>{u.phone}</span>
                            </div>
                          )}
                        </td>

                        {/* Rôle */}
                        <td className="py-4 px-4 capitalize font-semibold text-slate-700 dark:text-slate-300">
                          {u.role}
                        </td>

                        {/* Statut avec pastille pastel douce */}
                        <td className="py-4 px-4 whitespace-nowrap">
                          <Badge variante={u.is_active ? 'succes' : 'alerte'}>
                            {u.is_active ? 'Actif' : 'En attente'}
                          </Badge>
                        </td>

                        {/* Date inscription */}
                        <td className="py-4 px-4 text-slate-400 dark:text-slate-500 whitespace-nowrap">
                          {formaterDate(u.created_at)}
                        </td>

                        {/* Actions */}
                        <td className="py-4 px-4 sm:px-6 text-right space-x-2 whitespace-nowrap">
                          {!estMoi && (
                            <>
                              <Bouton
                                variante={u.is_active ? 'contour' : 'primaire'}
                                taille="sm"
                                onClick={() => basculerStatut(u.id)}
                                icone={
                                  u.is_active ? (
                                    <UserX className="w-3.5 h-3.5" />
                                  ) : (
                                    <UserCheck className="w-3.5 h-3.5" />
                                  )
                                }
                              >
                                {u.is_active ? 'Désactiver' : 'Valider'}
                              </Bouton>

                              <button
                                onClick={() => supprimerUtilisateur(u.id, u.name)}
                                className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                title="Supprimer le membre"
                              >
                                <Trash2 className="w-3.5 h-3.5" />
                              </button>
                            </>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            )}
          </div>
        )}
      </Carte>
    </div>
  );
};
