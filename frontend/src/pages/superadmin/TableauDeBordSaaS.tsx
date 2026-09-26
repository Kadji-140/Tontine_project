import React, { useEffect, useState } from 'react';
import api from '../../lib/api';
import { Carte } from '../../components/ui/Carte';
import { Badge } from '../../components/ui/Badge';
import { Bouton } from '../../components/ui/Bouton';
import { Link } from 'react-router-dom';
import { 
  Building2, 
  Users, 
  PiggyBank, 
  HandCoins, 
  ShieldCheck, 
  AlertCircle,
  PlusCircle,
  ArrowRight
} from 'lucide-react';
import { Tenant } from '../../types';

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

export const TableauDeBordSaaS: React.FC = () => {
  const [stats, setStats] = useState<StatistiquesGlobales | null>(null);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState<string | null>(null);

  const chargerStats = async () => {
    setChargement(true);
    setErreur(null);
    try {
      const reponse = await api.get<{ succes: boolean } & StatistiquesGlobales>('/super-admin/statistiques');
      if (reponse.data.succes) {
        setStats(reponse.data);
      }
    } catch (err: any) {
      setErreur(err.response?.data?.message || 'Impossible de charger les statistiques globales.');
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerStats();
  }, []);

  const formaterDevise = (montant: number) => {
    return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(montant);
  };

  if (chargement) {
    return (
      <div className="flex items-center justify-center min-h-[300px]">
        <div className="w-8 h-8 border-4 border-amber-600 border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (erreur || !stats) {
    return (
      <div className="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/50 flex items-center gap-3">
        <AlertCircle className="w-5 h-5 flex-shrink-0" />
        <p className="text-sm font-medium">{erreur || 'Erreur inconnue.'}</p>
        <Bouton variante="secondaire" taille="sm" onClick={chargerStats} className="ml-auto">
          Réessayer
        </Bouton>
      </div>
    );
  }

  const { kpis, tontines_recentes } = stats;

  return (
    <div className="space-y-8">
      {/* En-tête */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div className="flex items-center gap-2">
            <span className="px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider rounded-md bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
              Supervision SaaS
            </span>
            <h1 className="text-2xl font-black text-slate-900 dark:text-white">
              Tour de Contrôle Plateforme
            </h1>
          </div>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Supervision transversale de l'ensemble des tontines hébergées et de la trésorerie globale.
          </p>
        </div>

        <Link to="/super-admin/tenants">
          <Bouton variante="primaire" className="bg-amber-600 hover:bg-amber-700 text-white flex items-center gap-2">
            <PlusCircle className="w-4 h-4" />
            Créer une Organisation
          </Bouton>
        </Link>
      </div>

      {/* Cartes KPI */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* Tontines */}
        <Carte className="p-5 border-l-4 border-l-amber-500">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Organisations Tontines
            </span>
            <div className="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600">
              <Building2 className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <p className="text-3xl font-black text-slate-900 dark:text-white">
              {kpis.total_tontines}
            </p>
            <div className="flex items-center gap-2 mt-1 text-xs text-slate-500">
              <span className="font-semibold text-emerald-600">{kpis.tontines_actives} actives</span>
              <span>•</span>
              <span className="text-rose-500">{kpis.tontines_suspendues} suspendues</span>
            </div>
          </div>
        </Carte>

        {/* Membres globaux */}
        <Carte className="p-5 border-l-4 border-l-primaire-500">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Membres Inscrits
            </span>
            <div className="p-2 rounded-lg bg-primaire-50 dark:bg-primaire-950/60 text-primaire-600">
              <Users className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <p className="text-3xl font-black text-slate-900 dark:text-white">
              {kpis.total_utilisateurs}
            </p>
            <div className="flex items-center gap-2 mt-1 text-xs text-slate-500">
              <span className="font-semibold text-emerald-600">{kpis.utilisateurs_actifs} validés</span>
              {kpis.utilisateurs_en_attente > 0 && (
                <>
                  <span>•</span>
                  <span className="text-amber-500 font-semibold">{kpis.utilisateurs_en_attente} en sas d'attente</span>
                </>
              )}
            </div>
          </div>
        </Carte>

        {/* Volume Épargné */}
        <Carte className="p-5 border-l-4 border-l-emerald-500">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Volume Épargné Total
            </span>
            <div className="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600">
              <PiggyBank className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl font-black text-slate-900 dark:text-white">
              {formaterDevise(kpis.total_epargne_plateforme)} FCFA
            </p>
            <p className="text-xs text-slate-500 mt-1">
              Cumul des cotisations sur toutes les tontines
            </p>
          </div>
        </Carte>

        {/* Encours Crédits */}
        <Carte className="p-5 border-l-4 border-l-secondaire-500">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Encours Prêts Actifs
            </span>
            <div className="p-2 rounded-lg bg-secondaire-50 dark:bg-secondaire-950/60 text-secondaire-600">
              <HandCoins className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl font-black text-slate-900 dark:text-white">
              {formaterDevise(kpis.encours_credits)} FCFA
            </p>
            <p className="text-xs text-slate-500 mt-1">
              Remboursements totaux : {formaterDevise(kpis.total_rembourse)} FCFA
            </p>
          </div>
        </Carte>
      </div>

      {/* Tontines récentes */}
      <Carte className="p-6">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h2 className="text-base font-bold text-slate-900 dark:text-white">
              Organisations Récemment Créées
            </h2>
            <p className="text-xs text-slate-500">
              Dernières tontines actives ou enregistrées sur la plateforme.
            </p>
          </div>
          <Link to="/super-admin/tenants">
            <Bouton variante="fantome" taille="sm" className="text-amber-600 hover:text-amber-700 flex items-center gap-1.5">
              <span>Voir tout</span>
              <ArrowRight className="w-4 h-4" />
            </Bouton>
          </Link>
        </div>

        <div className="divide-y divide-slate-100 dark:divide-sombre-bordure">
          {tontines_recentes.map((tenant) => (
            <div key={tenant.id} className="py-3.5 flex items-center justify-between gap-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                  {tenant.nom.substring(0, 2).toUpperCase()}
                </div>
                <div>
                  <div className="flex items-center gap-2">
                    <p className="text-sm font-bold text-slate-900 dark:text-white">{tenant.nom}</p>
                    <Badge variante={tenant.statut === 'actif' ? 'succes' : 'danger'} taille="sm">
                      {tenant.statut}
                    </Badge>
                  </div>
                  <p className="text-xs text-slate-500">
                    Slug: <code className="font-mono text-[11px] text-amber-600">{tenant.slug}</code> • Devise: {tenant.devise} • {tenant.utilisateurs_count ?? 0} membres
                  </p>
                </div>
              </div>

              <div className="flex items-center gap-2">
                <Link to="/super-admin/tenants">
                  <Bouton variante="secondaire" taille="sm">Gérer</Bouton>
                </Link>
              </div>
            </div>
          ))}
        </div>
      </Carte>
    </div>
  );
};

export default TableauDeBordSaaS;
