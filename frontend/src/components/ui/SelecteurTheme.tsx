import React from 'react';
import { useTheme, ModeTheme } from '../../hooks/useTheme';
import { Sun, Moon, Laptop } from 'lucide-react';
import { cn } from '../../lib/utils';

export const SelecteurTheme: React.FC<{ compact?: boolean }> = ({ compact = false }) => {
  const { theme, setTheme, estSombre, basculerTheme } = useTheme();

  if (compact) {
    return (
      <button
        onClick={basculerTheme}
        aria-label="Basculer le thème"
        className="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-sombre-survol transition-colors"
      >
        {estSombre ? (
          <Sun className="w-5 h-5 text-amber-400 transition-transform rotate-0 hover:rotate-45" />
        ) : (
          <Moon className="w-5 h-5 text-slate-600 transition-transform rotate-0 hover:-rotate-12" />
        )}
      </button>
    );
  }

  const options: { mode: ModeTheme; etiquette: string; icone: React.ReactNode }[] = [
    { mode: 'clair', etiquette: 'Clair', icone: <Sun className="w-4 h-4" /> },
    { mode: 'sombre', etiquette: 'Sombre', icone: <Moon className="w-4 h-4" /> },
    { mode: 'systeme', etiquette: 'Auto', icone: <Laptop className="w-4 h-4" /> },
  ];

  return (
    <div className="flex items-center p-1 bg-slate-100 dark:bg-sombre-carte rounded-xl border border-slate-200 dark:border-sombre-bordure">
      {options.map((opt) => {
        const estActif = theme === opt.mode;
        return (
          <button
            key={opt.mode}
            onClick={() => setTheme(opt.mode)}
            className={cn(
              'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-lg transition-all',
              estActif
                ? 'bg-white dark:bg-sombre-surface text-primaire-600 dark:text-primaire-400 shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'
            )}
          >
            {opt.icone}
            <span>{opt.etiquette}</span>
          </button>
        );
      })}
    </div>
  );
};
