import React from 'react';
import { Search, X, Filter } from 'lucide-react';
import { cn } from '../../lib/utils';

export interface OptionFiltre {
  cle: string;
  label: string;
  compte?: number;
}

interface PropsBarreFiltresTableau {
  termeRecherche: string;
  surChangementRecherche: (valeur: string) => void;
  placeholder?: string;
  optionsFiltres?: OptionFiltre[];
  filtreActif?: string;
  surChangementFiltre?: (cle: string) => void;
  totalResultats?: number;
  libelleResultats?: string;
  actionsSupplementaires?: React.ReactNode;
}

/**
 * Composant réutilisable et responsive de barre de filtres et recherche pour tous les tableaux.
 * - S'adapte parfaitement sur mobile (colonne fluide, défilement horizontal des pilules)
 * - Rendu épuré Dribbble (fonds clairs, bordures fines, typographie élégante)
 */
export const BarreFiltresTableau: React.FC<PropsBarreFiltresTableau> = ({
  termeRecherche,
  surChangementRecherche,
  placeholder = 'Rechercher...',
  optionsFiltres = [],
  filtreActif,
  surChangementFiltre,
  totalResultats,
  libelleResultats = 'élément(s) trouvé(s)',
  actionsSupplementaires,
}) => {
  return (
    <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-3.5 mb-4">
      {/* Zone gauche : Champ de recherche et pilules de filtres */}
      <div className="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
        {/* Champ de recherche instantané */}
        <div className="relative w-full sm:w-72 md:w-80">
          <div className="flex items-center bg-white dark:bg-sombre-surface rounded-xl border border-slate-200/80 dark:border-sombre-bordure px-3 py-2 text-xs text-slate-400 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/10 transition-all shadow-2xs">
            <Search className="w-3.5 h-3.5 text-slate-400 mr-2 shrink-0" />
            <input
              type="text"
              value={termeRecherche}
              onChange={(e) => surChangementRecherche(e.target.value)}
              placeholder={placeholder}
              className="bg-transparent border-none outline-none text-slate-800 dark:text-slate-100 text-xs w-full placeholder:text-slate-400"
            />
            {termeRecherche && (
              <button
                onClick={() => surChangementRecherche('')}
                className="p-0.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition-colors ml-1"
                title="Effacer la recherche"
              >
                <X className="w-3 h-3" />
              </button>
            )}
          </div>
        </div>

        {/* Pilules de filtres avec défilement horizontal fluide sur mobile */}
        {optionsFiltres.length > 0 && surChangementFiltre && (
          <div className="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
            {optionsFiltres.map((option) => {
              const estActif = filtreActif === option.cle;
              return (
                <button
                  key={option.cle}
                  onClick={() => surChangementFiltre(option.cle)}
                  className={cn(
                    'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-150',
                    estActif
                      ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-2xs'
                      : 'bg-white dark:bg-sombre-surface text-slate-600 dark:text-slate-400 border border-slate-200/80 dark:border-sombre-bordure hover:bg-slate-50 dark:hover:bg-sombre-survol hover:text-slate-900'
                  )}
                >
                  <span>{option.label}</span>
                  {option.compte !== undefined && (
                    <span
                      className={cn(
                        'text-[10px] px-1.5 py-0.2 rounded-full font-bold',
                        estActif
                          ? 'bg-white/20 text-white dark:bg-slate-900/10 dark:text-slate-900'
                          : 'bg-slate-100 dark:bg-slate-800 text-slate-500'
                      )}
                    >
                      {option.compte}
                    </span>
                  )}
                </button>
              );
            })}
          </div>
        )}
      </div>

      {/* Zone droite : Compteur de résultats et actions contextuelles */}
      <div className="flex items-center justify-between sm:justify-end gap-3 shrink-0 text-xs">
        {totalResultats !== undefined && (
          <span className="text-slate-500 dark:text-slate-400 font-medium text-[11px]">
            <strong className="text-slate-800 dark:text-slate-200 font-bold">{totalResultats}</strong>{' '}
            {libelleResultats}
          </span>
        )}
        {actionsSupplementaires}
      </div>
    </div>
  );
};
