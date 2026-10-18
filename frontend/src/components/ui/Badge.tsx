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
    succes: 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20',
    danger: 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-500/20',
    alerte: 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200/60 dark:border-amber-500/20',
    info: 'bg-sky-50 dark:bg-sky-500/10 text-sky-700 dark:text-sky-400 border border-sky-200/60 dark:border-sky-500/20',
    neutre: 'bg-slate-100 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700/60',
    primaire: 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-500/20',
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
