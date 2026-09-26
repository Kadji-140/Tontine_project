import React from 'react';
import { NavLink } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { cn } from '../../lib/utils';
import {
  LayoutDashboard,
  HandCoins,
  PiggyBank,
  ListOrdered,
  User,
  Repeat,
  Calendar,
  Scale,
  Users,
  LogOut,
  Wallet,
  Shield,
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
  const { utilisateur, estBureau, deconnexion } = useAuthStore();

  const liensMembres = [
    { to: '/tableau-de-bord', label: 'Tableau de bord', icone: <LayoutDashboard className="w-5 h-5" /> },
    { to: '/prets', label: 'Mes Prêts', icone: <HandCoins className="w-5 h-5" /> },
    { to: '/mes-gains', label: 'Mes Gains', icone: <PiggyBank className="w-5 h-5" /> },
    { to: '/cycles', label: 'Cycles & Ordre', icone: <Repeat className="w-5 h-5" /> },
    { to: '/profil', label: 'Mon Profil', icone: <User className="w-5 h-5" /> },
  ];

  const liensBureau = [
    { to: '/seances', label: 'Séances & Réunions', icone: <Calendar className="w-5 h-5" /> },
    { to: '/sanctions', label: 'Sanctions & Amendes', icone: <Scale className="w-5 h-5" /> },
    { to: '/utilisateurs', label: 'Gestion des Membres', icone: <Users className="w-5 h-5" /> },
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
        {/* En-tête / Logo */}
        <div className="flex items-center justify-between h-20 px-6 border-b border-slate-100 dark:border-sombre-bordure">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-gradient-to-tr from-primaire-600 to-primaire-400 text-white flex items-center justify-center shadow-lg shadow-primaire-500/30">
              <Wallet className="w-6 h-6" />
            </div>
            <div>
              <span className="text-lg font-black tracking-tight text-slate-900 dark:text-white">
                Tontine<span className="text-primaire-500">Pro</span>
              </span>
              <p className="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Plateforme SaaS
              </p>
            </div>
          </div>

          <button
            onClick={surFermerMobile}
            className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 lg:hidden"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Profil condensé */}
        <div className="p-4 mx-4 my-4 rounded-2xl bg-slate-50 dark:bg-sombre-carte border border-slate-100 dark:border-sombre-bordure/60">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-primaire-100 dark:bg-primaire-950/60 text-primaire-700 dark:text-primaire-400 font-bold flex items-center justify-center flex-shrink-0 text-sm border border-primaire-200 dark:border-primaire-800">
              {utilisateur?.name?.charAt(0).toUpperCase() || 'U'}
            </div>
            <div className="min-w-0 flex-1">
              <p className="text-sm font-bold text-slate-800 dark:text-white truncate">
                {utilisateur?.name}
              </p>
              <div className="flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400 capitalize">
                {estBureau && <Shield className="w-3 h-3 text-primaire-500" />}
                <span>{utilisateur?.role}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Liens de navigation */}
        <nav className="flex-1 px-4 space-y-6 overflow-y-auto">
          <div>
            <p className="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
              Espace Membre
            </p>
            <div className="space-y-1">
              {liensMembres.map((lien) => (
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

          {estBureau && (
            <div>
              <p className="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Gestion du Bureau
              </p>
              <div className="space-y-1">
                {liensBureau.map((lien) => (
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
