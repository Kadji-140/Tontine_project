import React from 'react';
import { cn } from '../../lib/utils';

interface PropsSquelette {
  className?: string;
  style?: React.CSSProperties;
}

/**
 * Composant de base pour un bloc squelette avec animation douce de pulsation / shimmer
 */
export const Squelette: React.FC<PropsSquelette> = ({ className, style }) => {
  return (
    <div
      style={style}
      className={cn(
        'animate-pulse rounded-xl bg-slate-200/70 dark:bg-slate-800/60',
        className
      )}
    />
  );
};

/**
 * Squelette reproduisant la grande carte Dribbble (KPI + Graphique) pendant le chargement
 */
export const SqueletteCarteGraphique: React.FC = () => {
  return (
    <div className="bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/70 dark:border-sombre-bordure p-6 space-y-6">
      {/* 4 colonnes KPI */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pb-6 border-b border-slate-100 dark:border-sombre-bordure">
        {[1, 2, 3, 4].map((i) => (
          <div key={i} className="space-y-2">
            <div className="flex justify-between items-center">
              <Squelette className="h-3 w-24" />
              <Squelette className="h-4 w-10 rounded-full" />
            </div>
            <Squelette className="h-8 w-32" />
            <Squelette className="h-2.5 w-40" />
          </div>
        ))}
      </div>

      {/* Zone graphique */}
      <div className="h-72 w-full flex items-end gap-3 pt-6 px-4">
        {[40, 60, 45, 75, 55, 90, 65, 50, 70, 80].map((h, i) => (
          <div key={i} className="flex-1 flex flex-col items-center gap-2">
            <Squelette className="w-full rounded-t-lg" style={{ height: `${h}%` }} />
            <Squelette className="h-2.5 w-6" />
          </div>
        ))}
      </div>
    </div>
  );
};

/**
 * Squelette pour tableau d'activités ou listes
 */
export const SqueletteTableau: React.FC<{ lignes?: number }> = ({ lignes = 4 }) => {
  return (
    <div className="bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/70 dark:border-sombre-bordure p-6 space-y-4">
      <div className="flex justify-between items-center pb-2">
        <Squelette className="h-5 w-48" />
        <Squelette className="h-8 w-20 rounded-lg" />
      </div>

      <div className="space-y-3">
        {Array.from({ length: lignes }).map((_, i) => (
          <div
            key={i}
            className="flex items-center justify-between py-3 border-b border-slate-100 dark:border-sombre-bordure/40"
          >
            <div className="flex items-center gap-3">
              <Squelette className="w-9 h-9 rounded-full" />
              <div className="space-y-1.5">
                <Squelette className="h-3.5 w-32" />
                <Squelette className="h-2.5 w-24" />
              </div>
            </div>
            <Squelette className="h-6 w-20 rounded-md" />
            <Squelette className="h-4 w-28" />
            <Squelette className="h-4 w-24" />
          </div>
        ))}
      </div>
    </div>
  );
};
