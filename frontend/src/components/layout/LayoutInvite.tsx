import React from 'react';
import { Outlet } from 'react-router-dom';
import { Wallet } from 'lucide-react';
import { SelecteurTheme } from '../ui/SelecteurTheme';

export const LayoutInvite: React.FC = () => {
  return (
    <div className="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50 dark:bg-sombre-fond transition-colors">
      <div className="absolute top-6 right-6">
        <SelecteurTheme compact={true} />
      </div>

      <div className="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-primaire-600 to-primaire-400 text-white shadow-xl shadow-primaire-500/30 mb-4">
          <Wallet className="w-8 h-8" />
        </div>
        <h2 className="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
          Tontine<span className="text-primaire-500">Pro</span>
        </h2>
        <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
          La plateforme collaborative de vos tontines et épargnes
        </p>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div className="bg-white dark:bg-sombre-surface py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/50 dark:shadow-carte-sombre rounded-3xl border border-slate-100 dark:border-sombre-bordure">
          <Outlet />
        </div>
      </div>
    </div>
  );
};
