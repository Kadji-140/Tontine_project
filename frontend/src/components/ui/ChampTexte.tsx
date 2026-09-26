import React from 'react';
import { cn } from '../../lib/utils';

interface PropsChampTexte extends React.InputHTMLAttributes<HTMLInputElement> {
  etiquette?: string;
  erreur?: string;
  aide?: string;
  iconeGauche?: React.ReactNode;
  iconeDroite?: React.ReactNode;
}

export const ChampTexte = React.forwardRef<HTMLInputElement, PropsChampTexte>(({
  etiquette,
  erreur,
  aide,
  iconeGauche,
  iconeDroite,
  className,
  id,
  ...props
}, ref) => {
  const inputId = id || (etiquette ? etiquette.toLowerCase().replace(/\s+/g, '-') : undefined);

  return (
    <div className="w-full space-y-1.5">
      {etiquette && (
        <label
          htmlFor={inputId}
          className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300"
        >
          {etiquette}
        </label>
      )}

      <div className="relative rounded-xl shadow-sm">
        {iconeGauche && (
          <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-slate-500">
            {iconeGauche}
          </div>
        )}

        <input
          ref={ref}
          id={inputId}
          className={cn(
            'block w-full rounded-xl border border-slate-300 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2.5 px-3.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-colors focus:border-primaire-500 focus:outline-none focus:ring-2 focus:ring-primaire-500/20 disabled:opacity-50',
            iconeGauche && 'pl-10',
            iconeDroite && 'pr-10',
            erreur && 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20',
            className
          )}
          {...props}
        />

        {iconeDroite && (
          <div className="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 dark:text-slate-500">
            {iconeDroite}
          </div>
        )}
      </div>

      {erreur && (
        <p className="text-xs text-rose-500 font-medium">{erreur}</p>
      )}

      {aide && !erreur && (
        <p className="text-xs text-slate-500 dark:text-slate-400">{aide}</p>
      )}
    </div>
  );
});

ChampTexte.displayName = 'ChampTexte';
