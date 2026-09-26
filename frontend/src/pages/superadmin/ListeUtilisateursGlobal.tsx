import React, { useEffect, useState } from 'react';
import api from '../../lib/api';
import { Utilisateur } from '../../types';
import { Carte } from '../../components/ui/Carte';
import { Badge } from '../../components/ui/Badge';
import { Bouton } from '../../components/ui/Bouton';
import { 
  Users, 
  Search, 
  Crown, 
  Shield, 
  Building2, 
  CheckCircle2, 
  XCircle 
} from 'lucide-react';

export const ListeUtilisateursGlobal: React.FC = () => {
  const [utilisateurs, setUtilisateurs] = useState<Utilisateur[]>([]);
  const [chargement, setChargement] = useState(true);
  const [recherche, setRecherche] = useState('');
  const [filtreRole, setFiltreRole] = useState<'tous' | 'admin' | 'tresorier' | 'membre'>('tous');
  const [actionEnCours, setActionEnCours] = useState<number | null>(null);

  const chargerUtilisateurs = async () => {
    setChargement(true);
    try {
      const params: Record<string, string> = {};
      if (recherche) params.recherche = recherche;
      if (filtreRole !== 'tous') params.role = filtreRole;

      const reponse = await api.get<{ succes: boolean; utilisateurs: { data: Utilisateur[] } }>('/super-admin/utilisateurs', { params });
      if (reponse.data.succes) {
        setUtilisateurs(reponse.data.utilisateurs.data);
      }
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerUtilisateurs();
  }, [recherche, filtreRole]);

  const basculerSuperAdmin = async (user: Utilisateur) => {
    setActionEnCours(user.id);
    try {
      await api.post(`/super-admin/utilisateurs/${user.id}/promouvoir`, {
        est_super_admin: !user.est_super_admin,
      });
      chargerUtilisateurs();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Erreur lors de la modification des privilèges.');
    } finally {
      setActionEnCours(null);
    }
  };

  const basculerActivation = async (user: Utilisateur) => {
    setActionEnCours(user.id);
    try {
      await api.patch(`/super-admin/utilisateurs/${user.id}`, {
        is_active: !user.is_active,
      });
      chargerUtilisateurs();
    } catch {
      // Ignorer
    } finally {
      setActionEnCours(null);
    }
  };

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div>
        <h1 className="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
          <Users className="w-7 h-7 text-primaire-600" />
          Répertoire Transversal des Utilisateurs
        </h1>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Tous les membres inscrits sur la plateforme SaaS, toutes tontines confondues.
        </p>
      </div>

      {/* Barre de recherche et filtres */}
      <Carte className="p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Rechercher par nom, email, téléphone..."
            value={recherche}
            onChange={(e) => setRecherche(e.target.value)}
            className="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primaire-500"
          />
        </div>

        <div className="flex items-center gap-2 self-start sm:self-auto">
          {(['tous', 'admin', 'tresorier', 'membre'] as const).map((r) => (
            <button
              key={r}
              onClick={() => setFiltreRole(r)}
              className={`px-3 py-1.5 text-xs font-bold rounded-lg transition-colors capitalize ${
                filtreRole === r
                  ? 'bg-primaire-600 text-white'
                  : 'bg-slate-100 dark:bg-sombre-survol text-slate-600 dark:text-slate-400 hover:bg-slate-200'
              }`}
            >
              {r}
            </button>
          ))}
        </div>
      </Carte>

      {/* Tableau des utilisateurs */}
      <Carte className="overflow-hidden p-0">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead className="bg-slate-50 dark:bg-sombre-survol text-xs uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-sombre-bordure">
              <tr>
                <th className="py-3.5 px-4">Utilisateur</th>
                <th className="py-3.5 px-4">Organisation (Tenant)</th>
                <th className="py-3.5 px-4">Rôle Tontine</th>
                <th className="py-3.5 px-4">Statut Validation</th>
                <th className="py-3.5 px-4">Super-Admin</th>
                <th className="py-3.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure">
              {chargement ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400">
                    Chargement des utilisateurs...
                  </td>
                </tr>
              ) : utilisateurs.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400">
                    Aucun utilisateur trouvé.
                  </td>
                </tr>
              ) : (
                utilisateurs.map((u) => (
                  <tr key={u.id} className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/50 transition-colors">
                    <td className="py-4 px-4">
                      <div>
                        <div className="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
                          <span>{u.name}</span>
                          {u.est_super_admin && (
                            <span title="Super-Administrateur">
                              <Crown className="w-3.5 h-3.5 text-amber-500" />
                            </span>
                          )}
                        </div>
                        <p className="text-xs text-slate-400">{u.email} • {u.phone}</p>
                      </div>
                    </td>

                    <td className="py-4 px-4">
                      {u.tenant ? (
                        <div className="flex items-center gap-1.5">
                          <Building2 className="w-3.5 h-3.5 text-slate-400" />
                          <span className="font-medium text-slate-800 dark:text-slate-200">{u.tenant.nom}</span>
                        </div>
                      ) : (
                        <span className="text-xs text-amber-600 dark:text-amber-400 font-semibold italic">
                          Global (Aucun tenant)
                        </span>
                      )}
                    </td>

                    <td className="py-4 px-4">
                      <span className="capitalize font-semibold text-slate-700 dark:text-slate-300">
                        {u.role}
                      </span>
                    </td>

                    <td className="py-4 px-4">
                      <Badge variante={u.is_active ? 'succes' : 'alerte'}>
                        {u.is_active ? 'Validé' : 'En attente'}
                      </Badge>
                    </td>

                    <td className="py-4 px-4">
                      {u.est_super_admin ? (
                        <span className="inline-flex items-center gap-1 text-xs font-bold text-amber-600 dark:text-amber-400">
                          <Crown className="w-3.5 h-3.5" />
                          Oui
                        </span>
                      ) : (
                        <span className="text-xs text-slate-400">Non</span>
                      )}
                    </td>

                    <td className="py-4 px-4 text-right space-x-2">
                      <Bouton
                        variante={u.is_active ? 'secondaire' : 'primaire'}
                        taille="sm"
                        onClick={() => basculerActivation(u)}
                        chargement={actionEnCours === u.id}
                      >
                        {u.is_active ? 'Désactiver' : 'Valider'}
                      </Bouton>

                      <Bouton
                        variante={u.est_super_admin ? 'danger' : 'secondaire'}
                        taille="sm"
                        onClick={() => basculerSuperAdmin(u)}
                        chargement={actionEnCours === u.id}
                        className={u.est_super_admin ? '' : 'text-amber-600 hover:text-amber-700'}
                      >
                        {u.est_super_admin ? 'Révoquer SA' : 'Promouvoir SA'}
                      </Bouton>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </Carte>
    </div>
  );
};

export default ListeUtilisateursGlobal;
