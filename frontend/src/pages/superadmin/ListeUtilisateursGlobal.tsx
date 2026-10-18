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
  Clock,
  PowerOff,
  UserCheck,
  Filter,
  RotateCw,
  Sparkles
} from 'lucide-react';
import { cn } from '../../lib/utils';

export const ListeUtilisateursGlobal: React.FC = () => {
  const [utilisateurs, setUtilisateurs] = useState<Utilisateur[]>([]);
  const [chargement, setChargement] = useState(true);
  const [recherche, setRecherche] = useState('');
  const [filtreRole, setFiltreRole] = useState<'tous' | 'admin' | 'tresorier' | 'membre'>('tous');
  const [filtreValidation, setFiltreValidation] = useState<'tous' | 'valide' | 'en_attente'>('tous');
  const [actionEnCours, setActionEnCours] = useState<number | null>(null);

  const chargerUtilisateurs = async () => {
    setChargement(true);
    try {
      const params: Record<string, string> = {};
      if (recherche) params.recherche = recherche;
      if (filtreRole !== 'tous') params.role = filtreRole;
      if (filtreValidation !== 'tous') params.statut_validation = filtreValidation;

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
  }, [recherche, filtreRole, filtreValidation]);

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
    <div className="space-y-6 animate-in fade-in duration-300">
      {/* 1. En-tête */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60">
              Supervision SaaS
            </span>
            <span className="text-xs text-slate-400 dark:text-slate-500">• Répertoire Transversal</span>
          </div>
          <h1 className="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
            <Users className="w-6 h-6 text-blue-600" />
            Tous les Utilisateurs de la Plateforme
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Supervisez, validez et attribuez des privilèges aux membres de l'ensemble des tontines hébergées.
          </p>
        </div>

        <button
          onClick={() => chargerUtilisateurs()}
          disabled={chargement}
          className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-sombre-surface border border-slate-200 dark:border-sombre-bordure text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-sombre-survol transition-colors self-start sm:self-auto shrink-0 shadow-2xs"
        >
          <RotateCw className={cn("w-3.5 h-3.5", chargement && "animate-spin text-blue-600")} />
          <span>Actualiser</span>
        </button>
      </div>

      {/* 2. Filtres & Recherche Multi-Critères */}
      <div className="bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/80 dark:border-sombre-bordure p-4 sm:p-5 shadow-[0_1px_3px_rgba(0,0,0,0.03)] space-y-4">
        
        {/* Ligne 1 : Barre de recherche textuelle */}
        <div className="relative w-full">
          <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Rechercher par nom complet, adresse email, téléphone..."
            value={recherche}
            onChange={(e) => setRecherche(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-sombre-bordure bg-slate-50/50 dark:bg-sombre-surface text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-sombre-carte transition-all"
          />
        </div>

        {/* Ligne 2 : Sélecteurs de filtres (Rôle + Statut de Validation) */}
        <div className="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100 dark:border-sombre-bordure">
          
          {/* Filtre Statut de Validation (Sas d'attente vs Validé) */}
          <div className="flex items-center gap-1.5 flex-wrap">
            <span className="text-xs font-semibold text-slate-400 dark:text-slate-500 mr-1 flex items-center gap-1">
              <Filter className="w-3.5 h-3.5" />
              Validation :
            </span>
            <button
              onClick={() => setFiltreValidation('tous')}
              className={cn(
                "px-3 py-1.5 text-xs font-bold rounded-xl transition-all",
                filtreValidation === 'tous'
                  ? "bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs"
                  : "bg-slate-100 dark:bg-sombre-surface text-slate-600 dark:text-slate-400 hover:bg-slate-200/70"
              )}
            >
              Tous les comptes
            </button>
            <button
              onClick={() => setFiltreValidation('valide')}
              className={cn(
                "px-3 py-1.5 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5",
                filtreValidation === 'valide'
                  ? "bg-emerald-600 text-white shadow-xs"
                  : "bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100/70 border border-emerald-200/60 dark:border-emerald-900/40"
              )}
            >
              <CheckCircle2 className="w-3.5 h-3.5" />
              <span>Validés (Actifs)</span>
            </button>
            <button
              onClick={() => setFiltreValidation('en_attente')}
              className={cn(
                "px-3 py-1.5 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5",
                filtreValidation === 'en_attente'
                  ? "bg-amber-600 text-white shadow-xs"
                  : "bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 hover:bg-amber-100/70 border border-amber-200/60 dark:border-amber-900/40"
              )}
            >
              <Clock className="w-3.5 h-3.5" />
              <span>En sas d'attente</span>
            </button>
          </div>

          {/* Filtre Rôle Tontine */}
          <div className="flex items-center gap-1.5 flex-wrap">
            <span className="text-xs font-semibold text-slate-400 dark:text-slate-500 mr-1">Rôle :</span>
            {(['tous', 'admin', 'tresorier', 'membre'] as const).map((r) => (
              <button
                key={r}
                onClick={() => setFiltreRole(r)}
                className={cn(
                  "px-3 py-1 text-xs font-bold rounded-lg transition-all capitalize",
                  filtreRole === r
                    ? "bg-blue-600 text-white shadow-xs"
                    : "bg-slate-100 dark:bg-sombre-surface text-slate-600 dark:text-slate-400 hover:bg-slate-200/70"
                )}
              >
                {r === 'tous' ? 'Tous rôles' : r}
              </button>
            ))}
          </div>

        </div>
      </div>

      {/* 3. Tableau Épuré & Design Moderne des Utilisateurs */}
      <div className="bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/70 dark:border-sombre-bordure shadow-[0_1px_3px_rgba(0,0,0,0.03)] overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs sm:text-sm">
            <thead className="bg-slate-50/80 dark:bg-sombre-surface text-[11px] uppercase font-bold text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-sombre-bordure tracking-wider">
              <tr>
                <th className="py-3.5 px-4 font-semibold">Utilisateur</th>
                <th className="py-3.5 px-4 font-semibold">Organisation (Tenant)</th>
                <th className="py-3.5 px-4 font-semibold">Rôle Tontine</th>
                <th className="py-3.5 px-4 font-semibold">Statut Validation</th>
                <th className="py-3.5 px-4 font-semibold">Super-Admin</th>
                <th className="py-3.5 px-4 font-semibold text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure/60">
              {chargement ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400 text-xs">
                    Chargement des comptes utilisateurs...
                  </td>
                </tr>
              ) : utilisateurs.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400 text-xs">
                    Aucun utilisateur ne correspond aux critères de filtre.
                  </td>
                </tr>
              ) : (
                utilisateurs.map((u) => {
                  const estValide = Boolean(u.is_active);
                  const estSuper = Boolean(u.est_super_admin);
                  const enCours = actionEnCours === u.id;

                  return (
                    <tr
                      key={u.id}
                      className="hover:bg-slate-50/70 dark:hover:bg-sombre-survol/40 transition-colors"
                    >
                      {/* Colonne 1 : Nom, Avatar, Email & Téléphone */}
                      <td className="py-3.5 px-4">
                        <div className="flex items-center gap-3">
                          <div className={cn(
                            "w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs",
                            estSuper
                              ? "bg-gradient-to-tr from-amber-500 to-amber-700 text-white"
                              : "bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200"
                          )}>
                            {u.name?.charAt(0).toUpperCase() || 'U'}
                          </div>
                          <div className="min-w-0">
                            <div className="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white truncate">
                              <span className="truncate">{u.name}</span>
                              {estSuper && (
                                <span title="Super-Administrateur Plateforme">
                                  <Crown className="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                </span>
                              )}
                            </div>
                            <p className="text-[11px] text-slate-400 dark:text-slate-500 font-mono mt-0.5 truncate">
                              {u.email} • {u.phone}
                            </p>
                          </div>
                        </div>
                      </td>

                      {/* Colonne 2 : Organisation (Tenant) */}
                      <td className="py-3.5 px-4">
                        {u.tenant ? (
                          <div className="flex items-center gap-2">
                            <Building2 className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                            <div className="min-w-0">
                              <p className="font-semibold text-slate-800 dark:text-slate-200 truncate">
                                {u.tenant.nom}
                              </p>
                              <p className="text-[10px] text-slate-400 font-mono">@{u.tenant.slug}</p>
                            </div>
                          </div>
                        ) : (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/40">
                            Global (Sans tontine)
                          </span>
                        )}
                      </td>

                      {/* Colonne 3 : Rôle Tontine */}
                      <td className="py-3.5 px-4">
                        <span className={cn(
                          "px-2.5 py-0.5 rounded-full text-[11px] font-bold capitalize",
                          u.role === 'admin'
                            ? "bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/60"
                            : u.role === 'tresorier'
                            ? "bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/60"
                            : "bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                        )}>
                          {u.role}
                        </span>
                      </td>

                      {/* Colonne 4 : Statut Validation (Sas d'attente vs Validé) */}
                      <td className="py-3.5 px-4">
                        {estValide ? (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/60">
                            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" />
                            Validé
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200/60 animate-pulse">
                            <Clock className="w-3 h-3 text-amber-500" />
                            En attente
                          </span>
                        )}
                      </td>

                      {/* Colonne 5 : Statut Super-Admin */}
                      <td className="py-3.5 px-4">
                        {estSuper ? (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-black bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-300/80">
                            <Crown className="w-3 h-3 text-amber-600" />
                            Oui
                          </span>
                        ) : (
                          <span className="text-xs text-slate-400 font-medium">Non</span>
                        )}
                      </td>

                      {/* Colonne 6 : Boutons d'Action Modernes & Épurés (Flex horizontal nowrap) */}
                      <td className="py-3.5 px-4 text-right">
                        <div className="flex items-center justify-end gap-1.5 whitespace-nowrap">
                          {/* 1. Bouton Valider / Désactiver */}
                          {!estValide ? (
                            <button
                              onClick={() => basculerActivation(u)}
                              disabled={enCours}
                              className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition-all active:scale-95"
                              title="Valider et activer ce membre"
                            >
                              <CheckCircle2 className="w-3.5 h-3.5" />
                              <span>Valider</span>
                            </button>
                          ) : (
                            <button
                              onClick={() => basculerActivation(u)}
                              disabled={enCours}
                              className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-sombre-surface hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-sombre-bordure hover:border-rose-300 transition-all active:scale-95"
                              title="Désactiver ce compte"
                            >
                              <PowerOff className="w-3.5 h-3.5 text-slate-400 hover:text-rose-500" />
                              <span>Désactiver</span>
                            </button>
                          )}

                          {/* 2. Bouton Promouvoir / Révoquer Super-Admin */}
                          {estSuper ? (
                            <button
                              onClick={() => basculerSuperAdmin(u)}
                              disabled={enCours}
                              className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 shadow-2xs transition-all active:scale-95"
                              title="Révoquer les privilèges Super-Administrateur"
                            >
                              <Crown className="w-3.5 h-3.5 text-rose-600" />
                              <span>Révoquer SA</span>
                            </button>
                          ) : (
                            <button
                              onClick={() => basculerSuperAdmin(u)}
                              disabled={enCours}
                              className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-sombre-surface hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-950/40 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-sombre-bordure hover:border-amber-300 transition-all active:scale-95"
                              title="Promouvoir au rang de Super-Administrateur"
                            >
                              <Crown className="w-3.5 h-3.5 text-amber-500" />
                              <span>+ SA</span>
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default ListeUtilisateursGlobal;
