import React, { useState } from 'react';
import { Outlet } from 'react-router-dom';
import { BarreLaterale } from './BarreLaterale';
import { EnTete } from './EnTete';

export const LayoutApplication: React.FC = () => {
  const [menuMobileOuvert, setMenuMobileOuvert] = useState(false);

  return (
    <div className="flex h-screen overflow-hidden bg-slate-50 dark:bg-sombre-fond">
      {/* Barre latérale */}
      <BarreLaterale
        estOuverteMobile={menuMobileOuvert}
        surFermerMobile={() => setMenuMobileOuvert(false)}
      />

      {/* Contenu principal */}
      <div className="flex flex-col flex-1 min-w-0 overflow-hidden">
        <EnTete surOuvrirMobile={() => setMenuMobileOuvert(true)} />

        <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
          <div className="max-w-7xl mx-auto space-y-6">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
};
