import React from 'react';
import { cn } from '../../lib/utils';
import { Loader2 } from 'lucide-react';

interface PropsBouton extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variante?: 'primaire' | 'secondaire' | 'danger' | 'contour' | 'fantome';
  taille?: 'sm' | 'md' | 'lg';
  chargement?: boolean;
  icone?: React.ReactNode;
}

export const Bouton: React.FC<PropsBouton> = ({
  children,
  className,
  variante = 'primaire',
  taille = 'md',
  chargement = false,
  icone,
  disabled,
  ...props
}) => {
  const stylesVariante = {
    primaire: 'bg-primaire-600 hover:bg-primaire-700 text-white shadow-sm focus:ring-primaire-500',
    secondaire: 'bg-secondaire-600 hover:bg-secondaire-700 text-white shadow-sm focus:ring-secondaire-500',
    danger: 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm focus:ring-rose-500',
    contour: 'border border-slate-300 dark:border-sombre-bordure bg-white dark:bg-sombre-carte text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-sombre-survol',
    fantome: 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-sombre-survol hover:text-slate-900 dark:hover:text-white',
  };

  const stylesTaille = {
    sm: 'px-3 py-1.5 text-xs rounded-lg gap-1.5',
    md: 'px-4 py-2 text-sm rounded-xl gap-2',
    lg: 'px-6 py-2.5 text-base rounded-xl gap-2.5',
  };

  return (
    <button
      disabled={disabled || chargement}
      className={cn(
        'inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-sombre-fond disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]',
        stylesVariante[variante],
        stylesTaille[taille],
        className
      )}
      {...props}
    >
      {chargement ? (
        <Loader2 className="w-4 h-4 animate-spin text-current" />
      ) : icone ? (
        <span className="flex-shrink-0">{icone}</span>
      ) : null}
      <span>{children}</span>
    </button>
  );
};
