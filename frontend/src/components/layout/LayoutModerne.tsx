import React, { useState } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { BarreLateraleDribbble } from '../dashboard/nouveau/BarreLateraleDribbble';
import { SelecteurTheme } from '../ui/SelecteurTheme';
import { Menu, ShieldCheck } from 'lucide-react';
import { useAuthStore } from '../../stores/authStore';

/**
 * Titres automatiques des pages selon l'URL active
 */
const titresPages: Record<string, { titre: string; description: string }> = {
  '/tableau-de-bord': {
    titre: 'Tableau de bord',
    description: 'Supervision financière globale et performance du cycle',
  },
  '/seances': {
    titre: 'Séances & Réunions',
    description: 'Planning des assemblées, présences et versements du jour',
  },
  '/prets': {
    titre: 'Prêts & Crédits',
    description: 'Gestion des demandes d’emprunt, échéances et remboursements',
  },
  '/cycles': {
    titre: 'Cycles & Tirages',
    description: 'Rotations des bénéficiaires, cagnottes et ordres de passage',
  },
  '/mes-gains': {
    titre: 'Gains & Dividendes',
    description: 'Historique de vos tirages perçus et intérêts générés',
  },
  '/utilisateurs': {
    titre: 'Membres & Rôles',
    description: 'Annuaire des adhérents, droits d’accès et gestion du bureau',
  },
  '/sanctions': {
    titre: 'Sanctions & Règles',
    description: 'Discipline, amendes pour retards ou absences et règlements',
  },
  '/profil': {
    titre: 'Mon Profil & Paramètres',
    description: 'Informations personnelles, coordonnées et sécurité du compte',
  },
  '/super-admin': {
    titre: 'Tour de Contrôle SaaS',
    description: 'Supervision transversale de l’ensemble des organisations tontines hébergées',
  },
  '/super-admin/tenants': {
    titre: 'Organisations & Tontines',
    description: 'Gestion des organisations tontines, activation et quotas',
  },
  '/super-admin/utilisateurs': {
    titre: 'Tous les Utilisateurs',
    description: 'Supervision globale de tous les comptes enregistrés sur la plateforme',
  },
};

/**
 * Layout Moderne Unifié :
 * Héberge l'ensemble des pages de l'application dans le style épuré Dribbble,
 * servant de structure principale et élégante pour toute l'application.
 */
export const LayoutModerne: React.FC = () => {
  const [menuMobileOuvert, setMenuMobileOuvert] = useState(false);
  const location = useLocation();
  const { utilisateur, estSuperAdmin } = useAuthStore();

  let infoPage = titresPages[location.pathname];
  if (!infoPage) {
    if (location.pathname.startsWith('/seances/')) {
      infoPage = {
        titre: 'Détail de Séance',
        description: 'Feuille de présence, cotisations et opérations de séance',
      };
    } else {
      infoPage = {
        titre: 'Espace Tontine',
        description: 'Plateforme moderne de gestion collaborative',
      };
    }
  }

  return (
    <div className="h-screen w-screen overflow-hidden flex bg-[#f8fafc] dark:bg-sombre-fond text-slate-800 dark:text-slate-100 font-sans antialiased">
      {/* 1. Barre latérale Dribbble (fixe sur desktop, tiroir sur mobile) */}
      <BarreLateraleDribbble
        estOuvertMobile={menuMobileOuvert}
        surFermerMobile={() => setMenuMobileOuvert(false)}
      />

      {/* 2. Zone principale */}
      <div className="flex-1 flex flex-col h-full min-w-0 overflow-hidden">
        {/* En-tête supérieur sobre et épuré */}
        <header className="h-16 border-b border-slate-200/80 dark:border-sombre-bordure bg-white/80 dark:bg-sombre-surface/80 backdrop-blur-md px-6 flex items-center justify-between shrink-0 z-10">
          <div className="flex items-center gap-3">
            {/* Bouton menu mobile */}
            <button
              onClick={() => setMenuMobileOuvert(true)}
              className="p-1.5 rounded-lg border border-slate-200 dark:border-sombre-bordure text-slate-600 dark:text-slate-300 md:hidden hover:bg-slate-100"
            >
              <Menu className="w-4 h-4" />
            </button>

            <div>
              <div className="flex items-center gap-2">
                <h1 className="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                  {infoPage.titre}
                </h1>
                {estSuperAdmin ? (
                  <span className="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 dark:bg-amber-950/60 dark:text-amber-400 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-900/60">
                    <ShieldCheck className="w-3 h-3 text-amber-600" />
                    Supervision SaaS
                  </span>
                ) : (
                  <span className="text-[10px] font-bold text-slate-500 bg-slate-100 dark:bg-sombre-carte px-2 py-0.5 rounded-full border border-slate-200/60 dark:border-sombre-bordure">
                    {utilisateur?.tenant?.nom || 'Cycle 2026'}
                  </span>
                )}
              </div>
            </div>
          </div>

          {/* Actions à droite */}
          <div className="flex items-center gap-2 sm:gap-3">
            {/* Sélecteur de thème sombre / clair */}
            <SelecteurTheme />
          </div>
        </header>

        {/* Corps de page défilable contenant l'Outlet */}
        <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
          <div className="max-w-[1400px] mx-auto space-y-6">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
};

export default LayoutModerne;
