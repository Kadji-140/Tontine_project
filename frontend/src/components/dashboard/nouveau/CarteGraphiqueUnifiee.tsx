import React, { useState } from 'react';
import {
  ResponsiveContainer,
  AreaChart,
  Area,
  XAxis,
  YAxis,
  Tooltip,
  ReferenceLine,
} from 'recharts';
import { formaterMontant } from '../../../lib/utils';

/**
 * Données réelles ou projetées de la tontine par séance / mois
 */
interface PointTendanceTontine {
  mois: string;
  cotisationsReelles: number;
  tauxRecouvrement: number;
}

const pointsTontineParDefaut: PointTendanceTontine[] = [
  { mois: 'Jan', tauxRecouvrement: 47, cotisationsReelles: 2450000 },
  { mois: 'Fév', tauxRecouvrement: 38, cotisationsReelles: 1980000 },
  { mois: 'Mar', tauxRecouvrement: 40, cotisationsReelles: 2120000 },
  { mois: 'Avr', tauxRecouvrement: 35, cotisationsReelles: 1750000 },
  { mois: 'Mai', tauxRecouvrement: 45, cotisationsReelles: 2310000 },
  { mois: 'Juin', tauxRecouvrement: 64, cotisationsReelles: 3420000 },
  { mois: 'Juil', tauxRecouvrement: 44, cotisationsReelles: 2280000 }, // Point mis en exergue dans la maquette Dribbble (44%)
  { mois: 'Août', tauxRecouvrement: 35, cotisationsReelles: 1840000 },
  { mois: 'Sep', tauxRecouvrement: 43, cotisationsReelles: 2260000 },
  { mois: 'Oct', tauxRecouvrement: 45, cotisationsReelles: 2390000 },
];

interface PropsCarteGraphiqueUnifiee {
  soldeCaisse?: number;
  argentDehors?: number;
  totalCotisations?: number;
}

/**
 * Composant CarteGraphiqueUnifiee adapté 100% au système Tontine :
 * - 4 KPIs de la tontine directement intégrés au sommet avec cloisons verticales
 * - Graphique linéaire épuré avec courbe bleue, seuil d'objectif corail et infobulle noire
 * - Légende horizontale sobre
 */
export const CarteGraphiqueUnifiee: React.FC<PropsCarteGraphiqueUnifiee> = ({
  soldeCaisse = 14850000,
  argentDehors = 3200000,
  totalCotisations = 12500000,
}) => {
  // Seuil moyen de référence (Objectif mensuel de la tontine)
  const seuilObjectif = 39;

  return (
    <div className="bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/70 dark:border-sombre-bordure shadow-[0_1px_3px_rgba(0,0,0,0.03)] overflow-hidden">
      {/* 1. Rangée des indicateurs KPI intégrés (Style Dribbble avec cloisons verticales) */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 dark:divide-sombre-bordure p-6 pb-7">
        {/* KPI 1 : Taux de recouvrement des cotisations */}
        <div className="sm:pr-6 pb-4 sm:pb-0">
          <div className="flex items-center justify-between mb-1.5">
            <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
              Taux Recouvrement
            </span>
            <span className="text-xs font-bold px-2 py-0.5 rounded bg-[#ecfdf5] text-[#059669] dark:bg-emerald-950/60 dark:text-emerald-400">
              +5%
            </span>
          </div>
          <div className="text-3xl font-bold text-slate-900 dark:text-white tracking-tight tabular-nums">
            94%
          </div>
          <p className="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">
            Ponctualité des versements aux séances
          </p>
        </div>

        {/* KPI 2 : Cotisation Moyenne */}
        <div className="sm:px-6 py-4 sm:py-0">
          <div className="flex items-center justify-between mb-1.5">
            <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
              Cotisation Moyenne
            </span>
            <span className="text-xs font-bold px-2 py-0.5 rounded bg-[#ecfdf5] text-[#059669] dark:bg-emerald-950/60 dark:text-emerald-400">
              +12%
            </span>
          </div>
          <div className="text-3xl font-bold text-slate-900 dark:text-white tracking-tight tabular-nums">
            50 000 FCFA
          </div>
          <p className="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">
            Moyenne collectée par adhérent
          </p>
        </div>

        {/* KPI 3 : Argent dehors (Prêts & Micro-crédits) */}
        <div className="sm:px-6 py-4 sm:py-0">
          <div className="flex items-center justify-between mb-1.5">
            <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
              Crédits en Cours
            </span>
            <span className="text-xs font-bold px-2 py-0.5 rounded bg-[#fff1f2] text-[#e11d48] dark:bg-rose-950/60 dark:text-rose-400">
              -8%
            </span>
          </div>
          <div className="text-3xl font-bold text-slate-900 dark:text-white tracking-tight tabular-nums">
            {formaterMontant(argentDehors)}
          </div>
          <p className="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">
            Capital emprunté en cours de remboursement
          </p>
        </div>

        {/* KPI 4 : Solde Caisse Global */}
        <div className="sm:pl-6 pt-4 sm:pt-0">
          <div className="flex items-center justify-between mb-1.5">
            <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
              Caisse Totale Disponible
            </span>
          </div>
          <div className="text-3xl font-bold text-slate-900 dark:text-white tracking-tight truncate tabular-nums">
            {formaterMontant(soldeCaisse)}
          </div>
          <p className="text-[11px] text-slate-400 dark:text-slate-500 mt-1 truncate">
            Trésorerie nette en coffre & banque
          </p>
        </div>
      </div>

      {/* 2. Zone du Graphique Linéaire (Courbe bleue électrique + seuil corail en pointillés) */}
      <div className="h-72 w-full px-4 sm:px-6 pt-2">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart
            data={pointsTontineParDefaut}
            margin={{ top: 30, right: 10, left: -20, bottom: 0 }}
          >
            <defs>
              <linearGradient id="degradeBleuTontine" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stopColor="#2563eb" stopOpacity={0.12} />
                <stop offset="100%" stopColor="#2563eb" stopOpacity={0.0} />
              </linearGradient>
            </defs>

            {/* Abscisses : Mois */}
            <XAxis
              dataKey="mois"
              stroke="#94a3b8"
              fontSize={12}
              tickLine={false}
              axisLine={false}
              dy={10}
            />

            {/* Ordonnées : Pourcentage de réalisation */}
            <YAxis
              stroke="#94a3b8"
              fontSize={12}
              tickLine={false}
              axisLine={false}
              domain={[25, 80]}
              ticks={[30, 40, 50, 60, 70, 80]}
              tickFormatter={(v) => `${v}%`}
            />

            {/* Infobulle sombre flottante structurée et pédagogique */}
            <Tooltip
              cursor={{
                stroke: '#cbd5e1',
                strokeWidth: 1,
                strokeDasharray: '3 3',
              }}
              content={({ active, payload }) => {
                if (active && payload && payload.length) {
                  const donnee = payload[0].payload as PointTendanceTontine;
                  return (
                    <div className="relative -top-12 flex flex-col items-center">
                      <div className="bg-[#18181b] text-white p-3.5 rounded-xl shadow-2xl min-w-[210px] space-y-2 border border-slate-800 text-left">
                        {/* En-tête mois & badge */}
                        <div className="flex items-center justify-between border-b border-slate-800 pb-1.5">
                          <span className="text-xs font-bold text-slate-200">
                            Séance de {donnee.mois} 2026
                          </span>
                          <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-400">
                            {donnee.tauxRecouvrement}%
                          </span>
                        </div>

                        {/* Données chiffrées explicites */}
                        <div className="space-y-1.5 text-xs">
                          <div>
                            <span className="text-slate-400 text-[11px] block">
                              Total cotisations perçues :
                            </span>
                            <span className="font-bold text-white text-sm">
                              {formaterMontant(donnee.cotisationsReelles)}
                            </span>
                          </div>
                          <div className="flex items-center justify-between text-[11px] pt-1">
                            <span className="text-slate-400">Taux de recouvrement :</span>
                            <span className="font-semibold text-emerald-400">
                              {donnee.tauxRecouvrement}% de l'objectif
                            </span>
                          </div>
                          <div className="flex items-center justify-between text-[11px] pt-1 border-t border-slate-800 text-slate-400">
                            <span>Objectif mensuel de base :</span>
                            <span className="font-medium text-slate-300">
                              3 000 000 FCFA
                            </span>
                          </div>
                        </div>
                      </div>
                      <div className="w-0 h-0 border-l-[5px] border-l-transparent border-r-[5px] border-r-transparent border-t-[6px] border-t-[#18181b]" />
                    </div>
                  );
                }
                return null;
              }}
            />

            {/* Ligne repère horizontale en pointillé corail (Objectif cible de la tontine) */}
            <ReferenceLine
              y={seuilObjectif}
              stroke="#fca5a5"
              strokeDasharray="4 4"
              strokeWidth={1.5}
            />

            {/* Courbe bleue électrique élégante */}
            <Area
              type="monotone"
              dataKey="tauxRecouvrement"
              stroke="#2563eb"
              strokeWidth={2.5}
              fillOpacity={1}
              fill="url(#degradeBleuTontine)"
              activeDot={{
                r: 5,
                fill: '#2563eb',
                stroke: '#ffffff',
                strokeWidth: 3,
              }}
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>

      {/* 3. Légende horizontale sous le graphique avec intitulés clairs */}
      <div className="p-6 pt-3 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100/60 dark:border-sombre-bordure/40">
        <div className="flex flex-wrap items-center gap-6">
          <div className="flex items-center gap-2">
            <span className="w-2.5 h-2.5 rounded-[2px] bg-[#2563eb]" />
            <span className="font-medium text-slate-700 dark:text-slate-300">
              Taux de recouvrement des cotisations (Courbe bleue)
            </span>
          </div>

          <div className="flex items-center gap-2">
            <span className="w-4 h-0.5 border-t-2 border-dashed border-[#fb923c]" />
            <span className="font-medium text-slate-700 dark:text-slate-300">
              Objectif statutaire mensuel (Seuil corail : 3 000 000 FCFA)
            </span>
          </div>
        </div>

        <span className="text-[11px] text-slate-400">
          Survolez la courbe pour voir le montant collecté par séance
        </span>
      </div>
    </div>
  );
};
