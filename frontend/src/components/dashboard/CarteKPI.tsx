import React from 'react';
import { Carte } from '../ui/Carte';
import { formaterMontant } from '../../lib/utils';
import { cn } from '../../lib/utils';

interface PropsCarteKPI {
  titre: string;
  valeur: number | string;
  estMontant?: boolean;
  icone: React.ReactNode;
  couleur?: 'emeraude' | 'indigo' | 'ambre' | 'rose' | 'ardoise';
  description?: string;
}

export const CarteKPI: React.FC<PropsCarteKPI> = ({
  titre,
  valeur,
  estMontant = true,
  icone,
  couleur = 'emeraude',
  description,
}) => {
  const stylesCouleur = {
    emeraude: 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/60',
    indigo: 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/60',
    ambre: 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/60',
    rose: 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/60',
    ardoise: 'bg-slate-100 dark:bg-sombre-carte text-slate-600 dark:text-slate-300 border-slate-200 dark:border-sombre-bordure',
  };

  return (
    <Carte className="relative overflow-hidden">
      <div className="flex items-start justify-between">
        <div className="space-y-2">
          <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {titre}
          </p>
          <div className="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            {estMontant ? formaterMontant(valeur) : valeur}
          </div>
          {description && (
            <p className="text-xs text-slate-500 dark:text-slate-400 font-medium">
              {description}
            </p>
          )}
        </div>

        <div className={cn('p-3 rounded-2xl border', stylesCouleur[couleur])}>
          {icone}
        </div>
      </div>
    </Carte>
  );
};
