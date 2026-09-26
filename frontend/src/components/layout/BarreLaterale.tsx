import React from 'react';
import { NavLink } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { cn } from '../../lib/utils';
import {
  LayoutDashboard,
  HandCoins,
  PiggyBank,
  User,
  Repeat,
  Calendar,
  Scale,
  Users,
  LogOut,
  Wallet,
  Shield,
  Building2,
  Globe,
  Crown,
  X
} from 'lucide-react';

interface PropsBarreLaterale {
  estOuverteMobile?: boolean;
  surFermerMobile?: () => void;
}

export const BarreLaterale: React.FC<PropsBarreLaterale> = ({
  estOuverteMobile = false,
  surFermerMobile,
}) => {
  const { utilisateur, estSuperAdmin, deconnexion } = useAuthStore();
  const role = utilisateur?.role;

  // 1. Liens pour le Super-Administrateur (Gestion SaaS Plateforme Uniquement)
  const liensSuperAdmin = [
    { to: '/super-admin', label: 'Vue Globale SaaS', icone: <Globe className="w-5 h-5" /> },
    { to: '/super-admin/tenants', label: 'Gestion des Tontines', icone: <Building2 className="w-5 h-5" /> },
    { to: '/super-admin/utilisateurs', label: 'Tous les Utilisateurs', icone: <Users className="w-5 h-5" /> },
  ];

  // 2. Liens pour l'Administrateur de Tontine (Président)
  const liensAdminTontine = [
    { to: '/tableau-de-bord', label: 'Tableau de bord', icone: <LayoutDashboard className="w-5 h-5" /> },
    { to: '/cycles', label: 'Cycles & Ordre', icone: <Repeat className="w-5 h-5" /> },
    { to: '/seances', label: 'Séances & Réunions', icone: <Calendar className="w-5 h-5" /> },
    { to: '/utilisateurs', label: 'Gestion des Membres', icone: <Users className="w-5 h-5" /> },
    { to: '/sanctions', label: 'Sanctions & Amendes', icone: <Scale className="w-5 h-5" /> },
  ];

  // 3. Liens pour le Trésorier de Tontine (Caissier comptable)
  const liensTresorier = [
    { to: '/tableau-de-bord', label: 'Tableau de bord Caisse', icone: <LayoutDashboard className="w-5 h-5" /> },
    { to: '/seances', label: 'Séances & Cotisations', icone: <Calendar className="w-5 h-5" /> },
    { to: '/prets', label: 'Prêts & Décaissements', icone: <HandCoins className="w-5 h-5" /> },
    { to: '/sanctions', label: 'Sanctions & Pénalités', icone: <Scale className="w-5 h-5" /> },
    { to: '/utilisateurs', label: 'Annuaire des Membres', icone: <Users className="w-5 h-5" /> },
  ];

  // 4. Liens pour le Membre Standard (Épargnant)
  const liensMembreStandard = [
    { to: '/tableau-de-bord', label: 'Mon Espace Adhérent', icone: <LayoutDashboard className="w-5 h-5" /> },
    { to: '/prets', label: 'Mes Prêts', icone: <HandCoins className="w-5 h-5" /> },
    { to: '/mes-gains', label: 'Mes Gains & Tirages', icone: <PiggyBank className="w-5 h-5" /> },
    { to: '/cycles', label: 'Cycles & Ordre de Passage', icone: <Repeat className="w-5 h-5" /> },
  ];

  // Liens personnels pour l'admin / trésorier qui participe également
  const liensPersoBureau = [
    { to: '/prets', label: 'Mes Prêts Personnels', icone: <HandCoins className="w-5 h-5" /> },
    { to: '/mes-gains', label: 'Mes Gains Personnels', icone: <PiggyBank className="w-5 h-5" /> },
  ];

  return (
    <>
      {/* Overlay mobile */}
      {estOuverteMobile && (
        <div
          onClick={surFermerMobile}
          className="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity"
        />
      )}

      {/* Barre latérale */}
      <aside
        className={cn(
          'fixed inset-y-0 left-0 z-50 flex flex-col w-72 bg-white dark:bg-sombre-surface border-r border-slate-200 dark:border-sombre-bordure transition-transform duration-300 lg:translate-x-0 lg:static',
          estOuverteMobile ? 'translate-x-0' : '-translate-x-full'
        )}
      >
        {/* En-tête / Logo & Organisation */}
        <div className="p-6 border-b border-slate-100 dark:border-sombre-bordure">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className={cn(
                "p-2.5 rounded-xl shadow-md",
                estSuperAdmin 
                  ? "bg-amber-600 text-white shadow-amber-500/20"
                  : "bg-primaire-600 text-white shadow-primaire-500/20"
              )}>
                {estSuperAdmin ? <Crown className="w-6 h-6" /> : <Wallet className="w-6 h-6" />}
              </div>
              <div>
                <span className="text-xl font-black tracking-tight text-slate-900 dark:text-white">
                  Tontine<span className={estSuperAdmin ? "text-amber-600" : "text-primaire-600"}>Pro</span>
                </span>
                <p className="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                  {estSuperAdmin ? 'Supervision SaaS' : (utilisateur?.tenant?.nom || 'Organisation')}
                </p>
              </div>
            </div>

            {/* Bouton fermer sur mobile */}
            <button
              onClick={surFermerMobile}
              className="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white lg:hidden"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          {/* Profil utilisateur connecté */}
          <div className="mt-5 p-3 rounded-2xl bg-slate-50 dark:bg-sombre-survol/60 border border-slate-100 dark:border-sombre-bordure flex items-center gap-3">
            <div className={cn(
              "w-10 h-10 rounded-xl font-bold flex items-center justify-center text-sm shadow-sm",
              estSuperAdmin 
                ? "bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                : "bg-primaire-100 text-primaire-800 dark:bg-primaire-950 dark:text-primaire-300"
            )}>
              {utilisateur?.name?.charAt(0).toUpperCase() || 'U'}
            </div>
            <div className="min-w-0 flex-1">
              <p className="text-sm font-bold text-slate-800 dark:text-white truncate">
                {utilisateur?.name}
              </p>
              <div className="flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400 capitalize">
                {estSuperAdmin ? (
                  <>
                    <Crown className="w-3.5 h-3.5 text-amber-500" />
                    <span className="font-bold text-amber-600 dark:text-amber-400">Super-Admin SaaS</span>
                  </>
                ) : (
                  <>
                    {role === 'admin' && <Shield className="w-3.5 h-3.5 text-primaire-600" />}
                    {role === 'tresorier' && <Wallet className="w-3.5 h-3.5 text-secondaire-600" />}
                    <span>{role}</span>
                  </>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Liens de navigation selon le rôle */}
        <nav className="flex-1 px-4 py-4 space-y-6 overflow-y-auto">
          {/* CAS 1 : SUPER-ADMINISTRATEUR (Supervision SaaS pure) */}
          {estSuperAdmin && (
            <div className="space-y-4">
              <div>
                <p className="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                  <Crown className="w-3 h-3" />
                  Supervision Plateforme
                </p>
                <div className="space-y-1">
                  {liensSuperAdmin.map((lien) => (
                    <NavLink
                      key={lien.to}
                      to={lien.to}
                      onClick={surFermerMobile}
                      className={({ isActive }) =>
                        cn(
                          'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                          isActive
                            ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 shadow-sm border border-amber-200 dark:border-amber-900/60'
                            : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol hover:text-slate-900 dark:hover:text-white'
                        )
                      }
                    >
                      {lien.icone}
                      <span>{lien.label}</span>
                    </NavLink>
                  ))}
                </div>
              </div>

              <div>
                <p className="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                  Paramètres
                </p>
                <div className="space-y-1">
                  <NavLink
                    to="/profil"
                    onClick={surFermerMobile}
                    className={({ isActive }) =>
                      cn(
                        'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                        isActive
                          ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 shadow-sm'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol'
                      )
                    }
                  >
                    <User className="w-5 h-5" />
                    <span>Mon Profil</span>
                  </NavLink>
                </div>
              </div>
            </div>
          )}

          {/* CAS 2 : ADMINISTRATEUR DE TONTINE */}
          {!estSuperAdmin && role === 'admin' && (
            <div className="space-y-6">
              <div>
                <p className="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-primaire-600 dark:text-primaire-400">
                  Administration Tontine
                </p>
                <div className="space-y-1">
                  {liensAdminTontine.map((lien) => (
                    <NavLink
                      key={lien.to}
                      to={lien.to}
                      onClick={surFermerMobile}
                      className={({ isActive }) =>
                        cn(
                          'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                          isActive
                            ? 'bg-primaire-50 dark:bg-primaire-950/60 text-primaire-700 dark:text-primaire-400 shadow-sm border border-primaire-100 dark:border-primaire-900/60'
                            : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol hover:text-slate-900 dark:hover:text-white'
                        )
                      }
                    >
                      {lien.icone}
                      <span>{lien.label}</span>
                    </NavLink>
                  ))}
                </div>
              </div>

              <div>
                <p className="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                  Espace Personnel
                </p>
                <div className="space-y-1">
                  {liensPersoBureau.map((lien) => (
                    <NavLink
                      key={lien.to}
                      to={lien.to}
                      onClick={surFermerMobile}
                      className={({ isActive }) =>
                        cn(
                          'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                          isActive
                            ? 'bg-primaire-50 dark:bg-primaire-950/60 text-primaire-700 dark:text-primaire-400 shadow-sm'
                            : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol'
                        )
                      }
                    >
                      {lien.icone}
                      <span>{lien.label}</span>
                    </NavLink>
                  ))}
                  <NavLink
                    to="/profil"
                    onClick={surFermerMobile}
                    className={({ isActive }) =>
                      cn(
                        'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                        isActive
                          ? 'bg-primaire-50 dark:bg-primaire-950/60 text-primaire-700 dark:text-primaire-400 shadow-sm'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol'
                      )
                    }
                  >
                    <User className="w-5 h-5" />
                    <span>Mon Profil</span>
                  </NavLink>
                </div>
              </div>
            </div>
          )}

          {/* CAS 3 : TRÉSORIER */}
          {!estSuperAdmin && role === 'tresorier' && (
            <div className="space-y-6">
              <div>
                <p className="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-secondaire-600 dark:text-secondaire-400">
                  Gestion Trésorerie
                </p>
                <div className="space-y-1">
                  {liensTresorier.map((lien) => (
                    <NavLink
                      key={lien.to}
                      to={lien.to}
                      onClick={surFermerMobile}
                      className={({ isActive }) =>
                        cn(
                          'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                          isActive
                            ? 'bg-secondaire-50 dark:bg-secondaire-950/60 text-secondaire-700 dark:text-secondaire-400 shadow-sm border border-secondaire-100 dark:border-secondaire-900/60'
                            : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol hover:text-slate-900 dark:hover:text-white'
                        )
                      }
                    >
                      {lien.icone}
                      <span>{lien.label}</span>
                    </NavLink>
                  ))}
                </div>
              </div>

              <div>
                <p className="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                  Espace Personnel
                </p>
                <div className="space-y-1">
                  <NavLink
                    to="/mes-gains"
                    onClick={surFermerMobile}
                    className={({ isActive }) =>
                      cn(
                        'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                        isActive
                          ? 'bg-secondaire-50 dark:bg-secondaire-950/60 text-secondaire-700 dark:text-secondaire-400 shadow-sm'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol'
                      )
                    }
                  >
                    <PiggyBank className="w-5 h-5" />
                    <span>Mes Gains Personnels</span>
                  </NavLink>
                  <NavLink
                    to="/profil"
                    onClick={surFermerMobile}
                    className={({ isActive }) =>
                      cn(
                        'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                        isActive
                          ? 'bg-secondaire-50 dark:bg-secondaire-950/60 text-secondaire-700 dark:text-secondaire-400 shadow-sm'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol'
                      )
                    }
                  >
                    <User className="w-5 h-5" />
                    <span>Mon Profil</span>
                  </NavLink>
                </div>
              </div>
            </div>
          )}

          {/* CAS 4 : MEMBRE STANDARD */}
          {!estSuperAdmin && role === 'membre' && (
            <div>
              <p className="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Mon Espace Adhérent
              </p>
              <div className="space-y-1">
                {liensMembreStandard.map((lien) => (
                  <NavLink
                    key={lien.to}
                    to={lien.to}
                    onClick={surFermerMobile}
                    className={({ isActive }) =>
                      cn(
                        'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                        isActive
                          ? 'bg-primaire-50 dark:bg-primaire-950/60 text-primaire-700 dark:text-primaire-400 shadow-sm border border-primaire-100 dark:border-primaire-900/60'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol hover:text-slate-900 dark:hover:text-white'
                      )
                    }
                  >
                    {lien.icone}
                    <span>{lien.label}</span>
                  </NavLink>
                ))}
                <NavLink
                  to="/profil"
                  onClick={surFermerMobile}
                  className={({ isActive }) =>
                    cn(
                      'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all',
                      isActive
                        ? 'bg-primaire-50 dark:bg-primaire-950/60 text-primaire-700 dark:text-primaire-400 shadow-sm'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-sombre-survol'
                    )
                  }
                >
                  <User className="w-5 h-5" />
                  <span>Mon Profil</span>
                </NavLink>
              </div>
            </div>
          )}
        </nav>

        {/* Déconnexion */}
        <div className="p-4 border-t border-slate-100 dark:border-sombre-bordure">
          <button
            onClick={() => deconnexion()}
            className="flex items-center gap-3 w-full px-3.5 py-2.5 text-sm font-semibold rounded-xl text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
          >
            <LogOut className="w-5 h-5" />
            <span>Déconnexion</span>
          </button>
        </div>
      </aside>
    </>
  );
};

export default BarreLaterale;
