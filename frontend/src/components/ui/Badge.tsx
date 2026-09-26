import React from 'react';
import { cn } from '../../lib/utils';

interface PropsBadge extends React.HTMLAttributes<HTMLSpanElement> {
  variante?: 'succes' | 'danger' | 'alerte' | 'info' | 'neutre' | 'primaire';
  taille?: 'sm' | 'md';
}

export const Badge: React.FC<PropsBadge> = ({
  children,
  className,
  variante = 'neutre',
  taille = 'md',
  ...props
}) => {
  const stylesVariante = {
    succes: 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60',
    danger: 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60',
    alerte: 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60',
    info: 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60',
    neutre: 'bg-slate-100 dark:bg-sombre-carte text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-sombre-bordure',
    primaire: 'bg-primaire-50 dark:bg-primaire-950/60 text-primaire-700 dark:text-primaire-400 border border-primaire-200 dark:border-primaire-800/60',
  };

  const stylesTaille = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-xs font-semibold',
  };

  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full font-medium',
        stylesVariante[variante],
        stylesTaille[taille],
        className
      )}
      {...props}
    >
      {children}
    </span>
  );
};
