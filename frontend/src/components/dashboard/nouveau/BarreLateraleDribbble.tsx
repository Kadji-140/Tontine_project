import React from 'react';
import { NavLink, Link } from 'react-router-dom';
import {
  LayoutGrid,
  Calendar,
  Wallet,
  HandCoins,
  Repeat,
  Users,
  Scale,
  PiggyBank,
  BookOpen,
  Settings,
  Bell,
  Search,
  ChevronsUpDown,
  ArrowUpRight,
  LogOut,
  Building2,
  Globe,
  ShieldCheck
} from 'lucide-react';
import { cn } from '../../../lib/utils';
import { useAuthStore } from '../../../stores/authStore';

interface PropsBarreLateraleDribbble {
  estOuvertMobile?: boolean;
  surFermerMobile?: () => void;
}

/**
 * Barre latérale moderne respectant à 100% le style Dribbble épuré
 * Entièrement adaptée aux rôles de l'application (Super-Admin SaaS, Président, Trésorier, Membre)
 * sans aucun préfixe temporaire dans les URLs.
 */
export const BarreLateraleDribbble: React.FC<PropsBarreLateraleDribbble> = ({
  estOuvertMobile = false,
  surFermerMobile,
}) => {
  const { utilisateur, estSuperAdmin, deconnexion } = useAuthStore();
  const role = utilisateur?.role;

  // Détermination des liens de navigation selon le profil utilisateur
  let liensNavigation: Array<{
    to: string;
    label: string;
    icone: React.ReactNode;
    exact?: boolean;
  }> = [];

  if (estSuperAdmin) {
    liensNavigation = [
      {
        to: '/super-admin',
        label: 'Tour de Contrôle SaaS',
        icone: <Globe className="w-4 h-4" />,
        exact: true,
      },
      {
        to: '/super-admin/tenants',
        label: 'Organisations Tontines',
        icone: <Building2 className="w-4 h-4" />,
      },
      {
        to: '/super-admin/utilisateurs',
        label: 'Utilisateurs Globaux',
        icone: <Users className="w-4 h-4" />,
      },
    ];
  } else if (role === 'admin') {
    liensNavigation = [
      {
        to: '/tableau-de-bord',
        label: 'Tableau de bord',
        icone: <LayoutGrid className="w-4 h-4" />,
        exact: true,
      },
      {
        to: '/seances',
        label: 'Séances & Réunions',
        icone: <Calendar className="w-4 h-4" />,
      },
      {
        to: '/cycles',
        label: 'Cycles & Ordre',
        icone: <Repeat className="w-4 h-4" />,
      },
      {
        to: '/utilisateurs',
        label: 'Gestion des Membres',
        icone: <Users className="w-4 h-4" />,
      },
      {
        to: '/sanctions',
        label: 'Sanctions & Règles',
        icone: <Scale className="w-4 h-4" />,
      },
      {
        to: '/prets',
        label: 'Prêts & Crédits',
        icone: <HandCoins className="w-4 h-4" />,
      },
      {
        to: '/mes-gains',
        label: 'Mes Gains & Dividendes',
        icone: <PiggyBank className="w-4 h-4" />,
      },
    ];
  } else if (role === 'tresorier') {
    liensNavigation = [
      {
        to: '/tableau-de-bord',
        label: 'Tableau de bord Caisse',
        icone: <LayoutGrid className="w-4 h-4" />,
        exact: true,
      },
      {
        to: '/seances',
        label: 'Séances & Cotisations',
        icone: <Calendar className="w-4 h-4" />,
      },
      {
        to: '/prets',
        label: 'Prêts & Décaissements',
        icone: <HandCoins className="w-4 h-4" />,
      },
      {
        to: '/sanctions',
        label: 'Sanctions & Pénalités',
        icone: <Scale className="w-4 h-4" />,
      },
      {
        to: '/cycles',
        label: 'Cycles & Ordre',
        icone: <Repeat className="w-4 h-4" />,
      },
      {
        to: '/mes-gains',
        label: 'Mes Gains & Dividendes',
        icone: <PiggyBank className="w-4 h-4" />,
      },
      {
        to: '/utilisateurs',
        label: 'Annuaire des Membres',
        icone: <Users className="w-4 h-4" />,
      },
    ];
  } else {
    // Membre standard (Épargnant)
    liensNavigation = [
      {
        to: '/tableau-de-bord',
        label: 'Mon Espace Adhérent',
        icone: <LayoutGrid className="w-4 h-4" />,
        exact: true,
      },
      {
        to: '/prets',
        label: 'Mes Prêts & Crédits',
        icone: <HandCoins className="w-4 h-4" />,
      },
      {
        to: '/mes-gains',
        label: 'Mes Gains & Tirages',
        icone: <PiggyBank className="w-4 h-4" />,
      },
      {
        to: '/cycles',
        label: 'Cycles & Ordre de Passage',
        icone: <Repeat className="w-4 h-4" />,
      },
    ];
  }

  return (
    <>
      {/* Overlay sur mobile */}
      {estOuvertMobile && (
        <div
          onClick={surFermerMobile}
          className="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs md:hidden"
        />
      )}

      <aside
        className={cn(
          'fixed inset-y-0 left-0 z-50 md:static flex flex-col justify-between w-64 bg-[#f8fafc] dark:bg-sombre-surface border-r border-slate-200/80 dark:border-sombre-bordure h-full p-4 transition-transform duration-200 select-none',
          estOuvertMobile ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        )}
      >
        <div className="space-y-4">
          {/* 1. Sélecteur d'Organisation / Tontine (Style Dribbble : carte blanche avec icône) */}
          <div className="bg-white dark:bg-sombre-carte rounded-xl border border-slate-200/80 dark:border-sombre-bordure p-2.5 flex items-center justify-between shadow-[0_1px_2px_rgba(0,0,0,0.03)] cursor-pointer hover:border-slate-300 transition-colors">
            <div className="flex items-center gap-2.5 min-w-0">
              {/* Badge circulaire stylisé */}
              {estSuperAdmin ? (
                <div className="w-6 h-6 rounded-full bg-gradient-to-tr from-amber-500 to-amber-700 flex items-center justify-center text-white text-[11px] font-black shrink-0 shadow-xs">
                  <ShieldCheck className="w-3.5 h-3.5" />
                </div>
              ) : (
                <div className="w-6 h-6 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-[11px] font-black shrink-0 shadow-xs">
                  {utilisateur?.tenant?.nom ? utilisateur.tenant.nom.charAt(0).toUpperCase() : 'T'}
                </div>
              )}
              <div className="truncate">
                <p className="text-xs font-bold text-slate-900 dark:text-white truncate">
                  {estSuperAdmin ? 'Plateforme Tontine SaaS' : (utilisateur?.tenant?.nom || 'Tontine Pro')}
                </p>
                <p className="text-[10px] text-slate-400 dark:text-slate-500 font-medium truncate capitalize">
                  {estSuperAdmin ? 'Super-Administrateur' : (role ? `Rôle : ${role}` : 'Cycle Actif 2026')}
                </p>
              </div>
            </div>
            <ChevronsUpDown className="w-3.5 h-3.5 text-slate-400 shrink-0" />
          </div>

          {/* Onglets de navigation adaptés au contexte tontine */}
          <nav className="space-y-1 pt-1">
            {liensNavigation.map((lien) => (
              <NavLink
                key={lien.to}
                to={lien.to}
                onClick={surFermerMobile}
                end={lien.exact}
                className={({ isActive }) =>
                  cn(
                    'flex items-center gap-3 px-3 py-2 rounded-xl text-xs transition-all duration-150',
                    isActive
                      ? 'bg-slate-200/80 dark:bg-sombre-survol text-slate-900 dark:text-white font-bold shadow-2xs'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-sombre-survol/70 hover:text-slate-900 font-medium'
                  )
                }
              >
                {({ isActive }) => (
                  <>
                    <span className={isActive ? 'text-slate-900 dark:text-white' : 'text-slate-500'}>
                      {lien.icone}
                    </span>
                    <span className="truncate">{lien.label}</span>
                  </>
                )}
              </NavLink>
            ))}
          </nav>
        </div>

        {/* 4. Pied de barre latérale (Documentation, Paramètres, Notifications & Profil) */}
        <div className="space-y-3 pt-3 border-t border-slate-200/60 dark:border-sombre-bordure">
          <div className="space-y-1">
            {/* Documentation / Règlement */}
            <a
              href="#reglement"
              onClick={(e) => {
                e.preventDefault();
                alert(
                  estSuperAdmin
                    ? "Plateforme SaaS Tontine Pro : Gestion multi-tontines, étanchéité stricte des données et audits d'administration."
                    : "Règlement Intérieur : Les cotisations doivent être versées avant la clôture de chaque séance. Tout retard entraîne une pénalité statutaire."
                );
              }}
              className="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-sombre-survol cursor-pointer transition-colors"
            >
              <div className="flex items-center gap-3">
                <BookOpen className="w-4 h-4 text-slate-400" />
                <span>{estSuperAdmin ? 'Documentation SaaS' : 'Règlement intérieur'}</span>
              </div>
              <ArrowUpRight className="w-3.5 h-3.5 text-slate-400" />
            </a>

            {/* Profil & Paramètres */}
            <NavLink
              to="/profil"
              onClick={surFermerMobile}
              className={({ isActive }) =>
                cn(
                  'flex items-center gap-3 px-3 py-1.5 rounded-xl text-xs transition-colors',
                  isActive
                    ? 'bg-slate-200/80 dark:bg-sombre-survol text-slate-900 dark:text-white font-bold'
                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-sombre-survol'
                )
              }
            >
              <Settings className="w-4 h-4 text-slate-400" />
              <span>Mon profil & paramètres</span>
            </NavLink>

            {/* Notifications */}
            <div className="flex items-center justify-between px-3 py-1.5 rounded-xl text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-sombre-survol cursor-pointer transition-colors">
              <div className="flex items-center gap-3">
                <Bell className="w-4 h-4 text-slate-400" />
                <span>Notifications</span>
              </div>
              <span className="text-[10px] font-bold text-slate-600 dark:text-slate-300 bg-slate-200 dark:bg-sombre-survol px-1.5 py-0.2 rounded-full">
                {estSuperAdmin ? 'SaaS' : '3'}
              </span>
            </div>
          </div>

          {/* Profil connecté */}
          <div className="pt-2 border-t border-slate-200/40 dark:border-sombre-bordure flex items-center justify-between px-1">
            <div className="flex items-center gap-2.5 min-w-0">
              <div className="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-800 dark:text-white shrink-0">
                {utilisateur?.name?.charAt(0).toUpperCase() || 'U'}
              </div>
              <div className="min-w-0">
                <p className="text-xs font-bold text-slate-900 dark:text-white truncate">
                  {utilisateur?.name || 'Utilisateur'}
                </p>
                <p className="text-[10px] text-slate-400 dark:text-slate-500 truncate capitalize">
                  {estSuperAdmin ? 'Super-Admin' : (role || 'Membre')} • {utilisateur?.email}
                </p>
              </div>
            </div>

            <button
              onClick={deconnexion}
              title="Se déconnecter"
              className="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
            >
              <LogOut className="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </aside>
    </>
  );
};

export default BarreLateraleDribbble;
