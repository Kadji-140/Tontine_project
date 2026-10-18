import React, { useEffect, useState } from 'react';
import api from '../../lib/api';
import { Tenant } from '../../types';
import { formaterMontant, cn } from '../../lib/utils';
import { SqueletteCarteGraphique, SqueletteTableau } from '../../components/ui/SqueletteChargement';
import { Link } from 'react-router-dom';
import {
  Building2,
  Users,
  PiggyBank,
  HandCoins,
  Globe,
  ShieldCheck,
  AlertTriangle,
  PlusCircle,
  ArrowRight,
  RotateCw,
  Activity,
  CheckCircle2,
  Clock,
  ArrowUpRight,
  TrendingUp,
  Server
} from 'lucide-react';

interface StatistiquesGlobales {
  kpis: {
    total_tontines: number;
    tontines_actives: number;
    tontines_suspendues: number;
    total_utilisateurs: number;
    utilisateurs_actifs: number;
    utilisateurs_en_attente: number;
    total_epargne_plateforme: number;
    total_prets_accordes: number;
    total_rembourse: number;
    encours_credits: number;
  };
  tontines_recentes: Tenant[];
}

/**
 * Tableau de Bord Super-Administrateur (SaaS Multi-Tontines)
 * Design moderne épuré Dribbble 1:1 :
 * - Carte de métriques consolidées unifiée avec cloisons verticales
 * - Ratios de santé globale SaaS & monitoring multi-tenant
 * - Tableau épuré des organisations récentes
 * - Accès rapides aux actions d'administration
 */
export const TableauDeBordSaaS: React.FC = () => {
  const [stats, setStats] = useState<StatistiquesGlobales | null>(null);
  const [chargement, setChargement] = useState(true);
  const [actualisation, setActualisation] = useState(false);
  const [erreur, setErreur] = useState<string | null>(null);

  const chargerStats = async (estRafraichissement = false) => {
    if (estRafraichissement) setActualisation(true);
    else setChargement(true);
    setErreur(null);

    try {
      const reponse = await api.get<{ succes: boolean } & StatistiquesGlobales>('/super-admin/statistiques');
      if (reponse.data.succes) {
        setStats(reponse.data);
      }
    } catch (err: any) {
      setErreur(err.response?.data?.message || 'Impossible de charger les métriques globales.');
    } finally {
      setChargement(false);
      setActualisation(false);
    }
  };

  useEffect(() => {
    chargerStats();
  }, []);

  if (chargement) {
    return (
      <div className="space-y-6 animate-in fade-in duration-200">
        <SqueletteCarteGraphique />
        <SqueletteTableau lignes={5} />
      </div>
    );
  }

  if (erreur || !stats) {
    return (
      <div className="p-6 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 flex flex-col sm:flex-row items-center justify-between gap-4 text-rose-800 dark:text-rose-200">
        <div className="flex items-center gap-3">
          <AlertTriangle className="w-5 h-5 text-rose-600 shrink-0" />
          <p className="text-sm font-medium">{erreur || 'Erreur lors de la récupération des données SaaS.'}</p>
        </div>
        <button
          onClick={() => chargerStats()}
          className="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition-colors"
        >
          Réessayer
        </button>
      </div>
    );
  }

  const { kpis, tontines_recentes } = stats;

  // Ratios de santé calculés
  const tauxActifTontines = kpis.total_tontines > 0
    ? Math.round((kpis.tontines_actives / kpis.total_tontines) * 100)
    : 100;

  const tauxValidationMembres = kpis.total_utilisateurs > 0
    ? Math.round((kpis.utilisateurs_actifs / kpis.total_utilisateurs) * 100)
    : 100;

  const tauxRecouvrementPrets = kpis.total_prets_accordes > 0
    ? Math.round((kpis.total_rembourse / kpis.total_prets_accordes) * 100)
    : 100;

  return (
    <div className="space-y-6 animate-in fade-in duration-300">
      {/* 1. En-tête Supérieur Moderne */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60">
              <ShieldCheck className="w-3 h-3 text-amber-600" />
              Tour de Contrôle SaaS
            </span>
            <span className="text-xs text-slate-400 dark:text-slate-500">• Multi-Tenant Actif</span>
          </div>
          <h2 className="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            Supervision Globale de la Plateforme
          </h2>
          <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
            Gérez toutes les tontines hébergées, surveillez les volumes de liquidité et assurez l'intégrité du système.
          </p>
        </div>

        {/* Actions Rapides En-tête */}
        <div className="flex items-center gap-2 shrink-0">
          <button
            onClick={() => chargerStats(true)}
            disabled={actualisation}
            className="p-2.5 rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-sombre-survol transition-colors"
            title="Actualiser les métriques"
          >
            <RotateCw className={cn("w-4 h-4", actualisation && "animate-spin text-blue-600")} />
          </button>

          <Link
            to="/super-admin/tenants"
            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100 transition-all shadow-xs"
          >
            <PlusCircle className="w-4 h-4" />
            <span>Nouvelle Organisation</span>
          </Link>
        </div>
      </div>

      {/* 2. Grande Carte Unifiée : 4 Colonnes KPIs Consolidées (Style Dribbble 1:1) */}
      <div className="bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/70 dark:border-sombre-bordure shadow-[0_1px_3px_rgba(0,0,0,0.03)] overflow-hidden">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 dark:divide-sombre-bordure p-6 pb-7">
          
          {/* KPI 1 : Organisations Hébergées */}
          <div className="sm:pr-6 pb-4 sm:pb-0">
            <div className="flex items-center justify-between mb-1.5">
              <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
                Organisations Tontines
              </span>
              <span className="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                {kpis.tontines_actives} actives
              </span>
            </div>
            <div className="text-3xl font-black text-slate-900 dark:text-white tracking-tight tabular-nums">
              {kpis.total_tontines}
            </div>
            <p className="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">
              {kpis.tontines_suspendues > 0 ? `${kpis.tontines_suspendues} en pause / suspendue(s)` : '100% opérationnelles'}
            </p>
          </div>

          {/* KPI 2 : Total Utilisateurs Hébergés */}
          <div className="sm:px-6 py-4 sm:py-0">
            <div className="flex items-center justify-between mb-1.5">
              <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
                Membres Hébergés
              </span>
              <span className="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400">
                {kpis.utilisateurs_actifs} validés
              </span>
            </div>
            <div className="text-3xl font-black text-slate-900 dark:text-white tracking-tight tabular-nums">
              {kpis.total_utilisateurs}
            </div>
            <p className="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">
              {kpis.utilisateurs_en_attente > 0
                ? `${kpis.utilisateurs_en_attente} en sas d'attente d'adhésion`
                : 'Aucune demande en attente'}
            </p>
          </div>

          {/* KPI 3 : Volume Épargné Total */}
          <div className="sm:px-6 py-4 sm:py-0">
            <div className="flex items-center justify-between mb-1.5">
              <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
                Volume Épargné Consolidé
              </span>
              <span className="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                Flux Réel
              </span>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight tabular-nums">
              {formaterMontant(kpis.total_epargne_plateforme)}
            </div>
            <p className="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">
              Cumul des cotisations sur toutes les tontines
            </p>
          </div>

          {/* KPI 4 : Encours de Crédits Globaux */}
          <div className="sm:pl-6 pt-4 sm:pt-0">
            <div className="flex items-center justify-between mb-1.5">
              <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
                Encours Prêts Actifs
              </span>
              <span className="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400">
                {tauxRecouvrementPrets}% remboursé
              </span>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight tabular-nums">
              {formaterMontant(kpis.encours_credits)}
            </div>
            <p className="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">
              {formaterMontant(kpis.total_rembourse)} encaissés sur {formaterMontant(kpis.total_prets_accordes)}
            </p>
          </div>

        </div>

        {/* Bandeau d'état d'intégrité de la plateforme (Style Dribbble sobre) */}
        <div className="border-t border-slate-100 dark:border-sombre-bordure bg-slate-50/60 dark:bg-sombre-surface/40 px-6 py-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
          <div className="flex items-center gap-4">
            <div className="flex items-center gap-2">
              <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
              <span className="font-semibold text-slate-700 dark:text-slate-300">
                Isolation Multi-Tenant : Conforme
              </span>
            </div>
            <span className="text-slate-300 dark:text-slate-700 hidden sm:inline">|</span>
            <span className="text-slate-500 dark:text-slate-400 hidden sm:inline">
              Chaque requête est scellée par le Scope Tenant ID
            </span>
          </div>

          <div className="flex items-center gap-4">
            <Link
              to="/super-admin/utilisateurs"
              className="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1"
            >
              <span>Voir tous les utilisateurs</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </Link>
          </div>
        </div>
      </div>

      {/* 3. Section Grille : Santé SaaS (Gauche) & Raccourcis de Gestion (Droite) */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {/* Ratios et Santé de l'Écosystème (2 colonnes sur grand écran) */}
        <div className="lg:col-span-2 bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/70 dark:border-sombre-bordure shadow-[0_1px_3px_rgba(0,0,0,0.03)] p-6 sm:p-7">
          <div className="flex items-center justify-between mb-5">
            <div>
              <h3 className="text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <Activity className="w-4 h-4 text-emerald-600" />
                <span>Indicateurs de Santé de la Plateforme</span>
              </h3>
              <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                Ratios opérationnels mesurés en temps réel sur la base de données centrale
              </p>
            </div>
            <span className="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-lg">
              Système Stable
            </span>
          </div>

          <div className="space-y-4 pt-1">
            {/* Barre 1 : Taux d'organisations actives */}
            <div>
              <div className="flex items-center justify-between text-xs mb-1.5">
                <span className="font-semibold text-slate-700 dark:text-slate-300">
                  Taux d'Organisations Actives
                </span>
                <span className="font-bold text-slate-900 dark:text-white tabular-nums">
                  {tauxActifTontines}% ({kpis.tontines_actives}/{kpis.total_tontines})
                </span>
              </div>
              <div className="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div
                  className="h-full bg-emerald-500 rounded-full transition-all duration-500"
                  style={{ width: `${tauxActifTontines}%` }}
                />
              </div>
            </div>

            {/* Barre 2 : Taux de validation des membres */}
            <div>
              <div className="flex items-center justify-between text-xs mb-1.5">
                <span className="font-semibold text-slate-700 dark:text-slate-300">
                  Membres Validés vs En Attente
                </span>
                <span className="font-bold text-slate-900 dark:text-white tabular-nums">
                  {tauxValidationMembres}% ({kpis.utilisateurs_actifs}/{kpis.total_utilisateurs})
                </span>
              </div>
              <div className="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div
                  className="h-full bg-blue-500 rounded-full transition-all duration-500"
                  style={{ width: `${tauxValidationMembres}%` }}
                />
              </div>
            </div>

            {/* Barre 3 : Taux de remboursement des prêts */}
            <div>
              <div className="flex items-center justify-between text-xs mb-1.5">
                <span className="font-semibold text-slate-700 dark:text-slate-300">
                  Recouvrement des Crédits Octroyés
                </span>
                <span className="font-bold text-slate-900 dark:text-white tabular-nums">
                  {tauxRecouvrementPrets}% remboursé
                </span>
              </div>
              <div className="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div
                  className="h-full bg-amber-500 rounded-full transition-all duration-500"
                  style={{ width: `${tauxRecouvrementPrets}%` }}
                />
              </div>
            </div>
          </div>

          {/* Grille de 3 mini-cartes de conformité */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-6 pt-6 border-t border-slate-100 dark:border-sombre-bordure">
            <div className="p-3.5 rounded-xl bg-slate-50 dark:bg-sombre-surface/70 border border-slate-100 dark:border-sombre-bordure">
              <p className="text-[11px] font-semibold text-slate-500">Séparation Données</p>
              <p className="text-xs font-bold text-slate-900 dark:text-white mt-0.5">TenantScope Global</p>
              <span className="text-[10px] text-emerald-600 font-semibold">100% Hermétique</span>
            </div>

            <div className="p-3.5 rounded-xl bg-slate-50 dark:bg-sombre-surface/70 border border-slate-100 dark:border-sombre-bordure">
              <p className="text-[11px] font-semibold text-slate-500">Membres en Sas</p>
              <p className="text-xs font-bold text-slate-900 dark:text-white mt-0.5">
                {kpis.utilisateurs_en_attente} en attente
              </p>
              <span className={cn("text-[10px] font-semibold", kpis.utilisateurs_en_attente > 0 ? "text-amber-600" : "text-emerald-600")}>
                {kpis.utilisateurs_en_attente > 0 ? 'Validation requise' : 'À jour'}
              </span>
            </div>

            <div className="p-3.5 rounded-xl bg-slate-50 dark:bg-sombre-surface/70 border border-slate-100 dark:border-sombre-bordure">
              <p className="text-[11px] font-semibold text-slate-500">Devise Principale</p>
              <p className="text-xs font-bold text-slate-900 dark:text-white mt-0.5">XAF (FCFA)</p>
              <span className="text-[10px] text-slate-400 font-semibold">Multi-devises supporté</span>
            </div>
          </div>
        </div>

        {/* Accès Rapides & Actions Administratives (1 colonne) */}
        <div className="bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/70 dark:border-sombre-bordure shadow-[0_1px_3px_rgba(0,0,0,0.03)] p-6 sm:p-7 flex flex-col justify-between">
          <div>
            <h3 className="text-base font-bold text-slate-900 dark:text-white tracking-tight mb-1">
              Actions d'Administration
            </h3>
            <p className="text-xs text-slate-400 dark:text-slate-500 mb-5">
              Raccourcis rapides pour le pilotage de la plateforme
            </p>

            <div className="space-y-2.5">
              <Link
                to="/super-admin/tenants"
                className="flex items-center justify-between p-3 rounded-xl border border-slate-200/80 dark:border-sombre-bordure hover:border-blue-400 dark:hover:border-blue-500 bg-white dark:bg-sombre-surface transition-all group"
              >
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center shrink-0">
                    <Building2 className="w-4 h-4" />
                  </div>
                  <div>
                    <p className="text-xs font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">
                      Gérer les Tontines
                    </p>
                    <p className="text-[10px] text-slate-400">Créer, activer ou suspendre</p>
                  </div>
                </div>
                <ArrowRight className="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform" />
              </Link>

              <Link
                to="/super-admin/utilisateurs"
                className="flex items-center justify-between p-3 rounded-xl border border-slate-200/80 dark:border-sombre-bordure hover:border-emerald-400 dark:hover:border-emerald-500 bg-white dark:bg-sombre-surface transition-all group"
              >
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
                    <Users className="w-4 h-4" />
                  </div>
                  <div>
                    <p className="text-xs font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 transition-colors">
                      Membres & Comptes
                    </p>
                    <p className="text-[10px] text-slate-400">Valider les sas d'attente</p>
                  </div>
                </div>
                <ArrowRight className="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform" />
              </Link>

              <Link
                to="/profil"
                className="flex items-center justify-between p-3 rounded-xl border border-slate-200/80 dark:border-sombre-bordure hover:border-amber-400 dark:hover:border-amber-500 bg-white dark:bg-sombre-surface transition-all group"
              >
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center shrink-0">
                    <ShieldCheck className="w-4 h-4" />
                  </div>
                  <div>
                    <p className="text-xs font-bold text-slate-900 dark:text-white group-hover:text-amber-600 transition-colors">
                      Sécurité Super-Admin
                    </p>
                    <p className="text-[10px] text-slate-400">Identifiants et accès racine</p>
                  </div>
                </div>
                <ArrowRight className="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform" />
              </Link>
            </div>
          </div>

          <div className="pt-4 mt-4 border-t border-slate-100 dark:border-sombre-bordure text-center">
            <span className="text-[11px] text-slate-400 dark:text-slate-500">
              Version SaaS v2.0 • Hébergement Isolé
            </span>
          </div>
        </div>

      </div>

      {/* 4. Tableau Épuré : Organisations Tontines Récentes (Style Dribbble 1:1) */}
      <div className="bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/70 dark:border-sombre-bordure shadow-[0_1px_3px_rgba(0,0,0,0.03)] p-6 sm:p-7">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h3 className="text-lg font-bold text-slate-900 dark:text-white tracking-tight">
              Organisations Récemment Enregistrées
            </h3>
            <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
              Dernières structures tontinières créées sur la plateforme avec décompte des membres
            </p>
          </div>

          <Link
            to="/super-admin/tenants"
            className="px-3.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-sombre-survol transition-colors shadow-2xs inline-flex items-center gap-1.5"
          >
            <span>Toutes les organisations</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </Link>
        </div>

        {tontines_recentes.length === 0 ? (
          <div className="text-center py-10 text-slate-400 text-xs">
            Aucune organisation créée pour le moment.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead>
                <tr className="text-xs text-slate-400 dark:text-slate-500 font-normal border-b border-slate-100 dark:border-sombre-bordure/60">
                  <th className="pb-3.5 font-medium">Organisation & Slug</th>
                  <th className="pb-3.5 font-medium">Statut SaaS</th>
                  <th className="pb-3.5 font-medium">Membres Rattachés</th>
                  <th className="pb-3.5 font-medium">Cycles de Rotation</th>
                  <th className="pb-3.5 font-medium text-right">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure/40">
                {tontines_recentes.map((tenant) => {
                  const estActif = tenant.statut === 'actif';
                  return (
                    <tr
                      key={tenant.id}
                      className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/40 transition-colors"
                    >
                      {/* Colonne 1 : Organisation & Slug */}
                      <td className="py-4">
                        <div className="flex items-center gap-3">
                          <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-900 dark:from-slate-700 dark:to-slate-800 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-2xs">
                            {tenant.nom.substring(0, 2).toUpperCase()}
                          </div>
                          <div className="min-w-0">
                            <p className="text-xs font-bold text-slate-900 dark:text-white truncate">
                              {tenant.nom}
                            </p>
                            <p className="text-[11px] text-slate-400 dark:text-slate-500 font-mono mt-0.5 truncate">
                              @{tenant.slug} • {tenant.devise || 'FCFA'}
                            </p>
                          </div>
                        </div>
                      </td>

                      {/* Colonne 2 : Statut */}
                      <td className="py-4">
                        <span
                          className={cn(
                            'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold capitalize',
                            estActif
                              ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400'
                              : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400'
                          )}
                        >
                          <span
                            className={cn(
                              'w-1.5 h-1.5 rounded-full',
                              estActif ? 'bg-emerald-500' : 'bg-rose-500'
                            )}
                          />
                          {tenant.statut}
                        </span>
                      </td>

                      {/* Colonne 3 : Membres */}
                      <td className="py-4">
                        <div className="flex items-center gap-1.5 text-xs font-semibold text-slate-800 dark:text-slate-200">
                          <Users className="w-3.5 h-3.5 text-slate-400" />
                          <span>{tenant.utilisateurs_count ?? 0} membres</span>
                        </div>
                      </td>

                      {/* Colonne 4 : Cycles */}
                      <td className="py-4">
                        <span className="text-xs font-medium text-slate-600 dark:text-slate-400">
                          {tenant.cycles_count ?? 0} cycle(s) configuré(s)
                        </span>
                      </td>

                      {/* Colonne 5 : Actions */}
                      <td className="py-4 text-right">
                        <Link
                          to="/super-admin/tenants"
                          className="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-sombre-survol transition-colors"
                        >
                          <span>Gérer</span>
                          <ArrowRight className="w-3.5 h-3.5 text-slate-400" />
                        </Link>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};

export default TableauDeBordSaaS;
