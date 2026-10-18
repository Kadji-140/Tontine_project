import React from 'react';
import { formaterMontant, cn } from '../../../lib/utils';
import { PiggyBank, ShieldCheck, Landmark, ArrowUpRight } from 'lucide-react';

/**
 * Propriétés attendues pour le composant RepartitionTresorerie
 */
interface ProprietesRepartitionTresorerie {
  soldeCaisseGlobal?: number;
  totalTontine?: number;
  totalSecours?: number;
  totalPretsSortis?: number;
}

/**
 * Composant présentant la ventilation de la caisse sous forme de barres de progression
 * et indicateurs précis pour une vision stratégique immédiate.
 */
export const RepartitionTresorerie: React.FC<ProprietesRepartitionTresorerie> = ({
  soldeCaisseGlobal = 14850000,
  totalTontine = 10400000,
  totalSecours = 2650000,
  totalPretsSortis = 1800000,
}) => {
  const totalFonds = (totalTontine || 0) + (totalSecours || 0) + (totalPretsSortis || 0) || 1;

  const pctTontine = Math.round(((totalTontine || 0) / totalFonds) * 100);
  const pctSecours = Math.round(((totalSecours || 0) / totalFonds) * 100);
  const pctPrets = Math.max(0, 100 - pctTontine - pctSecours);

  const categories = [
    {
      titre: 'Épargne & Tontine',
      montant: totalTontine,
      pourcentage: pctTontine,
      couleurBarre: 'bg-emerald-500',
      couleurFond: 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300',
      icone: <PiggyBank className="w-4 h-4 text-emerald-600 dark:text-emerald-400" />,
      description: 'Cagnottes rotatives membres',
    },
    {
      titre: 'Fonds de Secours',
      montant: totalSecours,
      pourcentage: pctSecours,
      couleurBarre: 'bg-indigo-500',
      couleurFond: 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300',
      icone: <ShieldCheck className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />,
      description: 'Solidarité & imprévus sociaux',
    },
    {
      titre: 'Crédits & Intérêts Prêts',
      montant: totalPretsSortis,
      pourcentage: pctPrets,
      couleurBarre: 'bg-amber-500',
      couleurFond: 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300',
      icone: <Landmark className="w-4 h-4 text-amber-600 dark:text-amber-400" />,
      description: 'Fonds alloués aux micro-crédits',
    },
  ];

  return (
    <div className="relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-sombre-bordure bg-white dark:bg-sombre-carte p-6 shadow-[0_2px_12px_-4px_rgba(0,0,0,0.04)] flex flex-col justify-between">
      <div>
        <div className="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-sombre-bordure">
          <div>
            <h4 className="text-base font-bold text-slate-900 dark:text-white tracking-tight">
              Ventilation des Fonds
            </h4>
            <p className="text-xs text-slate-400 dark:text-slate-500">
              Répartition globale de la trésorerie active
            </p>
          </div>
          <div className="text-right">
            <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400">
              Total Caisse
            </span>
            <p className="text-sm font-black text-slate-900 dark:text-white">
              {formaterMontant(soldeCaisseGlobal)}
            </p>
          </div>
        </div>

        {/* Barre segmentée continue */}
        <div className="w-full h-3 rounded-full bg-slate-100 dark:bg-sombre-surface overflow-hidden flex mb-5">
          <div
            style={{ width: `${pctTontine}%` }}
            className="h-full bg-emerald-500 transition-all duration-500"
            title={`Tontine : ${pctTontine}%`}
          />
          <div
            style={{ width: `${pctSecours}%` }}
            className="h-full bg-indigo-500 transition-all duration-500"
            title={`Secours : ${pctSecours}%`}
          />
          <div
            style={{ width: `${pctPrets}%` }}
            className="h-full bg-amber-500 transition-all duration-500"
            title={`Prêts : ${pctPrets}%`}
          />
        </div>

        {/* Liste détaillée des catégories */}
        <div className="space-y-3.5">
          {categories.map((cat, idx) => (
            <div key={idx} className="flex items-center justify-between text-xs">
              <div className="flex items-center gap-2.5">
                <div className={cn('p-1.5 rounded-lg', cat.couleurFond)}>
                  {cat.icone}
                </div>
                <div>
                  <p className="font-bold text-slate-800 dark:text-slate-200">
                    {cat.titre}
                  </p>
                  <p className="text-[11px] text-slate-400 dark:text-slate-500">
                    {cat.description}
                  </p>
                </div>
              </div>

              <div className="text-right">
                <p className="font-extrabold text-slate-900 dark:text-white">
                  {formaterMontant(cat.montant)}
                </p>
                <span className="text-[11px] font-semibold text-slate-400">
                  {cat.pourcentage}%
                </span>
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="mt-5 pt-3 border-t border-slate-100 dark:border-sombre-bordure flex items-center justify-between text-[11px] text-slate-400">
        <span>Taux de rotation : Élevé</span>
        <span className="font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
          <ArrowUpRight className="w-3.5 h-3.5" />
          Fonds sécurisés à 100%
        </span>
      </div>
    </div>
  );
};
