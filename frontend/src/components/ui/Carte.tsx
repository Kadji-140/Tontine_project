import React from 'react';
import { cn } from '../../lib/utils';

interface PropsCarte extends React.HTMLAttributes<HTMLDivElement> {
  surbrillance?: boolean;
}

export const Carte: React.FC<PropsCarte> = ({
  children,
  className,
  surbrillance = false,
  ...props
}) => {
  return (
    <div
      className={cn(
        'rounded-2xl border border-slate-200/80 dark:border-sombre-bordure bg-white dark:bg-sombre-surface p-6 shadow-sm dark:shadow-carte-sombre transition-colors',
        surbrillance && 'ring-2 ring-primaire-500/20 border-primaire-500/40',
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
};

export const EnteteCarte: React.FC<React.HTMLAttributes<HTMLDivElement>> = ({
  children,
  className,
  ...props
}) => (
  <div className={cn('flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-sombre-bordure', className)} {...props}>
    {children}
  </div>
);

export const TitreCarte: React.FC<React.HTMLAttributes<HTMLHeadingElement>> = ({
  children,
  className,
  ...props
}) => (
  <h3 className={cn('text-base font-bold text-slate-900 dark:text-white', className)} {...props}>
    {children}
  </h3>
);

export const SousTitreCarte: React.FC<React.HTMLAttributes<HTMLParagraphElement>> = ({
  children,
  className,
  ...props
}) => (
  <p className={cn('text-xs text-slate-500 dark:text-slate-400 mt-0.5', className)} {...props}>
    {children}
  </p>
);
