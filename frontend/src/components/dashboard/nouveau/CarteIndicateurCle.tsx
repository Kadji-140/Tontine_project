import React from 'react';
import { cn } from '../../../lib/utils';
import { formaterMontant } from '../../../lib/utils';
import { TrendingUp, TrendingDown, Minus } from 'lucide-react';

/**
 * Propriétés attendues pour la carte d'indicateur clé (KPI)
 */
interface ProprietesCarteIndicateurCle {
  /** Titre ou libellé de l'indicateur */
  titre: string;
  /** Valeur principale (numérique ou textuelle) */
  valeur: number | string;
  /** Indique s'il faut formater la valeur en devise (FCFA) */
  estMontant?: boolean;
  /** Pourcentage de tendance ou variation (ex: +22, -15, 0) */
  variationPourcentage?: number;
  /** Texte descriptif ou contexte complémentaire */
  description?: string;
  /** Icône facultative pour enrichir visuellement la carte */
  icone?: React.ReactNode;
  /** Couleur thématique de mise en avant (par défaut : émeraude) */
  couleur?: 'emeraude' | 'indigo' | 'ambre' | 'rose' | 'ardoise';
}

/**
 * Composant d'affichage d'un indicateur clé dans le style épuré de la maquette Dribbble.
 * Présente le libellé en haut à gauche, un badge de tendance en haut à droite,
 * et une typographie claire et affirmée pour le montant ou le pourcentage.
 */
export const CarteIndicateurCle: React.FC<ProprietesCarteIndicateurCle> = ({
  titre,
  valeur,
  estMontant = true,
  variationPourcentage,
  description,
  icone,
  couleur = 'emeraude',
}) => {
  // Styles d'arrière-plan légers pour l'icône décorative
  const stylesFondsIcone = {
    emeraude: 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400',
    indigo: 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400',
    ambre: 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400',
    rose: 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400',
    ardoise: 'bg-slate-100 dark:bg-sombre-surface text-slate-600 dark:text-slate-300',
  };

  // Calcul du style du badge de tendance (+ vert, - rouge, neutre gris)
  const estPositif = variationPourcentage !== undefined && variationPourcentage > 0;
  const estNegatif = variationPourcentage !== undefined && variationPourcentage < 0;

  return (
    <div className="relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-sombre-bordure bg-white dark:bg-sombre-carte p-5 shadow-[0_2px_12px_-4px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_24px_-6px_rgba(0,0,0,0.08)] dark:hover:shadow-[0_8px_24px_-6px_rgba(0,0,0,0.4)] transition-all duration-300 group">
      {/* Ligne supérieure : Titre et Badge de progression */}
      <div className="flex items-center justify-between gap-2 mb-3">
        <div className="flex items-center gap-2">
          {icone && (
            <div className={cn('p-1.5 rounded-lg text-sm transition-transform duration-200 group-hover:scale-105', stylesFondsIcone[couleur])}>
              {icone}
            </div>
          )}
          <span className="text-xs font-medium text-slate-500 dark:text-slate-400 tracking-tight">
            {titre}
          </span>
        </div>

        {/* Badge pilule de tendance (inspiration directe Dribbble : fond pastel doux et texte contrasté) */}
        {variationPourcentage !== undefined && (
          <span
            className={cn(
              'inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[11px] font-semibold tracking-tight transition-colors',
              estPositif && 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/50',
              estNegatif && 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/50',
              !estPositif && !estNegatif && 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700'
            )}
          >
            {estPositif && <TrendingUp className="w-3 h-3 text-emerald-600 dark:text-emerald-400" />}
            {estNegatif && <TrendingDown className="w-3 h-3 text-rose-600 dark:text-rose-400" />}
            {!estPositif && !estNegatif && <Minus className="w-3 h-3" />}
            <span>{estPositif ? `+${variationPourcentage}%` : `${variationPourcentage}%`}</span>
          </span>
        )}
      </div>

      {/* Valeur principale mise en valeur */}
      <div className="flex items-baseline justify-between mt-1">
        <h3 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
          {estMontant && typeof valeur === 'number' ? formaterMontant(valeur) : valeur}
        </h3>
      </div>

      {/* Description ou sous-texte contextuel */}
      {description && (
        <p className="mt-2 text-xs text-slate-400 dark:text-slate-500 font-normal truncate">
          {description}
        </p>
      )}

      {/* Trait de couleur subtil en bas de carte */}
      <div
        className={cn(
          'absolute bottom-0 left-0 right-0 h-[2px] opacity-0 group-hover:opacity-100 transition-opacity duration-300',
          couleur === 'emeraude' && 'bg-emerald-500',
          couleur === 'indigo' && 'bg-indigo-500',
          couleur === 'ambre' && 'bg-amber-500',
          couleur === 'rose' && 'bg-rose-500',
          couleur === 'ardoise' && 'bg-slate-500'
        )}
      />
    </div>
  );
};
