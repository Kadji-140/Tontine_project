import React from 'react';
import { cn } from '../../lib/utils';
import { AlertCircle, CheckCircle2, Info, AlertTriangle } from 'lucide-react';

interface PropsAlerte extends React.HTMLAttributes<HTMLDivElement> {
  variante?: 'info' | 'succes' | 'alerte' | 'danger';
  titre?: string;
}

export const Alerte: React.FC<PropsAlerte> = ({
  children,
  className,
  variante = 'info',
  titre,
  ...props
}) => {
  const icones = {
    info: <Info className="w-5 h-5 text-sky-500 flex-shrink-0" />,
    succes: <CheckCircle2 className="w-5 h-5 text-emerald-500 flex-shrink-0" />,
    alerte: <AlertTriangle className="w-5 h-5 text-amber-500 flex-shrink-0" />,
    danger: <AlertCircle className="w-5 h-5 text-rose-500 flex-shrink-0" />,
  };

  const styles = {
    info: 'bg-sky-50/80 dark:bg-sky-950/40 border-sky-200 dark:border-sky-900/60 text-sky-900 dark:text-sky-200',
    succes: 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-900/60 text-emerald-900 dark:text-emerald-200',
    alerte: 'bg-amber-50/80 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900/60 text-amber-900 dark:text-amber-200',
    danger: 'bg-rose-50/80 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900/60 text-rose-900 dark:text-rose-200',
  };

  return (
    <div
      role="alert"
      className={cn(
        'flex gap-3 rounded-2xl border p-4 text-sm shadow-sm transition-colors',
        styles[variante],
        className
      )}
      {...props}
    >
      <div className="pt-0.5">{icones[variante]}</div>
      <div className="flex-1 space-y-1">
        {titre && <h5 className="font-semibold">{titre}</h5>}
        <div className="text-xs leading-relaxed opacity-90">{children}</div>
      </div>
    </div>
  );
};
