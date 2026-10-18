import React, { useState, useEffect } from 'react';
import {
  ResponsiveContainer,
  AreaChart,
  Area,
  XAxis,
  YAxis,
  Tooltip,
  CartesianGrid,
  ReferenceLine,
} from 'recharts';
import { formaterMontant, cn } from '../../../lib/utils';
import { useTheme } from '../../../hooks/useTheme';
import api from '../../../lib/api';
import { Loader2, TrendingUp, Calendar, Info } from 'lucide-react';

/**
 * Structure d'un point de données mensuel pour le graphique
 */
interface PointGraphique {
  /** Libellé du mois ou de la date (ex: Jan, Fév, Mar...) */
  periode: string;
  /** Montant réel des cotisations & encaissements */
  cotisationsReelles: number;
  /** Objectif ou référence moyenne attendue */
  objectifBenchmark: number;
  /** Pourcentage de réalisation par rapport à l'objectif */
  tauxRealisation?: number;
}

/**
 * Propriétés attendues pour le composant GraphiqueEvolutionTontine
 */
interface ProprietesGraphiqueEvolutionTontine {
  /** Données optionnelles fournies par le parent */
  donneesInitiales?: PointGraphique[];
  /** Titre personnalisable du graphique */
  titre?: string;
  /** Sous-titre explicatif */
  sousTitre?: string;
}

/**
 * Données d'exemple réalistes (style Dribbble) affichées si la base est encore vierge.
 * Permet un aperçu immédiat et fidèle à la maquette.
 */
const donneesParDefaut: PointGraphique[] = [
  { periode: 'Jan', cotisationsReelles: 3200000, objectifBenchmark: 3000000, tauxRealisation: 106 },
  { periode: 'Fév', cotisationsReelles: 2850000, objectifBenchmark: 3000000, tauxRealisation: 95 },
  { periode: 'Mar', cotisationsReelles: 3100000, objectifBenchmark: 3000000, tauxRealisation: 103 },
  { periode: 'Avr', cotisationsReelles: 2700000, objectifBenchmark: 3000000, tauxRealisation: 90 },
  { periode: 'Mai', cotisationsReelles: 3450000, objectifBenchmark: 3000000, tauxRealisation: 115 },
  { periode: 'Juin', cotisationsReelles: 4800000, objectifBenchmark: 3000000, tauxRealisation: 160 },
  { periode: 'Juil', cotisationsReelles: 3500000, objectifBenchmark: 3000000, tauxRealisation: 116 },
  { periode: 'Août', cotisationsReelles: 2600000, objectifBenchmark: 3000000, tauxRealisation: 86 },
  { periode: 'Sep', cotisationsReelles: 3300000, objectifBenchmark: 3000000, tauxRealisation: 110 },
  { periode: 'Oct', cotisationsReelles: 3650000, objectifBenchmark: 3000000, tauxRealisation: 121 },
];

/**
 * Composant graphique moderne inspiré de Dribbble :
 * - Courbe lisse (Spline monotone)
 * - Dégradé fluide émeraude / bleu
 * - Ligne d'objectif / benchmark en pointillé
 * - Infobulle foncée flottante avec indicateur de performance
 */
export const GraphiqueEvolutionTontine: React.FC<ProprietesGraphiqueEvolutionTontine> = ({
  donneesInitiales,
  titre = 'Évolution des Cotisations & Trésorerie',
  sousTitre = 'Performance mensuelle vs objectif du cycle',
}) => {
  const [periodeActive, setPeriodeActive] = useState<'today' | 'month' | 'year'>('year');
  const [chargement, setChargement] = useState(false);
  const [donnees, setDonnees] = useState<PointGraphique[]>(donneesInitiales || donneesParDefaut);
  const { estSombre } = useTheme();

  // Chargement des données réelles depuis le backend avec fallback intelligent
  useEffect(() => {
    const recupererDonneesGraphique = async () => {
      setChargement(true);
      try {
        const reponse = await api.get<{
          succes: boolean;
          labels: string[];
          encaisse: number[];
          depenses: number[];
          banque: number[];
        }>(`/dashboard/graphique?filtre=${periodeActive}`);

        if (reponse.data.succes && reponse.data.labels.length > 0) {
          const moyenneMois =
            reponse.data.encaisse.reduce((acc, curr) => acc + curr, 0) /
              (reponse.data.encaisse.length || 1) || 3000000;

          const pointsFormates: PointGraphique[] = reponse.data.labels.map((label, index) => {
            const encaisse = reponse.data.encaisse[index] || 0;
            const objectif = Math.max(moyenneMois, 500000);
            return {
              periode: label,
              cotisationsReelles: encaisse,
              objectifBenchmark: Math.round(objectif),
              tauxRealisation: Math.round((encaisse / objectif) * 100),
            };
          });

          // Si le backend renvoie des données avec au moins une valeur non nulle, on les affiche
          const aDesDonnees = pointsFormates.some((p) => p.cotisationsReelles > 0);
          setDonnees(aDesDonnees ? pointsFormates : donneesParDefaut);
        }
      } catch {
        // En cas d'erreur ou d'absence de connexion, conserver les données de démonstration
        setDonnees(donneesParDefaut);
      } finally {
        setChargement(false);
      }
    };

    recupererDonneesGraphique();
  }, [periodeActive]);

  // Filtres disponibles
  const optionsPeriodes = [
    { cle: 'today' as const, libelle: '7 Jours' },
    { cle: 'month' as const, libelle: 'Ce mois' },
    { cle: 'year' as const, libelle: 'Année 2026' },
  ];

  // Calcul du benchmark moyen pour la ligne de référence
  const valeurBenchmark =
    donnees.length > 0
      ? donnees[0].objectifBenchmark || 3000000
      : 3000000;

  return (
    <div className="relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-sombre-bordure bg-white dark:bg-sombre-carte p-6 shadow-[0_2px_12px_-4px_rgba(0,0,0,0.04)]">
      {/* En-tête du graphique : Titre, sous-titre et sélecteur de période */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-sombre-bordure">
        <div>
          <div className="flex items-center gap-2">
            <div className="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
              <TrendingUp className="w-4 h-4" />
            </div>
            <h4 className="text-base font-bold text-slate-900 dark:text-white tracking-tight">
              {titre}
            </h4>
          </div>
          <p className="mt-1 text-xs text-slate-400 dark:text-slate-500">
            {sousTitre}
          </p>
        </div>

        {/* Boutons de sélection de période (Pill tabs style Dribbble) */}
        <div className="flex items-center p-1 bg-slate-100/80 dark:bg-sombre-surface rounded-xl border border-slate-200/60 dark:border-sombre-bordure self-start sm:self-auto">
          {optionsPeriodes.map((option) => (
            <button
              key={option.cle}
              onClick={() => setPeriodeActive(option.cle)}
              className={cn(
                'px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200',
                periodeActive === option.cle
                  ? 'bg-white dark:bg-sombre-carte text-slate-900 dark:text-white shadow-sm'
                  : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
              )}
            >
              {option.libelle}
            </button>
          ))}
        </div>
      </div>

      {/* Zone du graphique Recharts */}
      <div className="h-80 w-full pt-6">
        {chargement ? (
          <div className="h-full flex flex-col items-center justify-center text-slate-400 space-y-2">
            <Loader2 className="w-6 h-6 animate-spin text-emerald-600" />
            <span className="text-xs font-medium">Actualisation du graphique...</span>
          </div>
        ) : (
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={donnees} margin={{ top: 20, right: 15, left: -10, bottom: 5 }}>
              <defs>
                {/* Dégradé doux et moderne sous la courbe de cotisations */}
                <linearGradient id="degradeTontineModerne" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stopColor="#059669" stopOpacity={0.22} />
                  <stop offset="60%" stopColor="#10b981" stopOpacity={0.06} />
                  <stop offset="100%" stopColor="#10b981" stopOpacity={0.0} />
                </linearGradient>

                {/* Dégradé subtil pour la zone de benchmark */}
                <linearGradient id="degradeBenchmark" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stopColor="#f97316" stopOpacity={0.08} />
                  <stop offset="100%" stopColor="#f97316" stopOpacity={0.0} />
                </linearGradient>
              </defs>

              {/* Grille d'arrière-plan discrète */}
              <CartesianGrid
                strokeDasharray="3 3"
                stroke={estSombre ? '#1f293d' : '#f1f5f9'}
                vertical={false}
              />

              {/* Axe des abscisses (Mois / Dates) */}
              <XAxis
                dataKey="periode"
                stroke={estSombre ? '#64748b' : '#94a3b8'}
                fontSize={12}
                tickLine={false}
                axisLine={false}
                dy={8}
              />

              {/* Axe des ordonnées (Montants en K FCFA) */}
              <YAxis
                stroke={estSombre ? '#64748b' : '#94a3b8'}
                fontSize={11}
                tickLine={false}
                axisLine={false}
                tickFormatter={(valeur) => `${(valeur / 1000).toLocaleString('fr-FR')}k`}
                dx={-4}
              />

              {/* Infobulle sombre moderne flottante comme sur le modèle Dribbble */}
              <Tooltip
                cursor={{
                  stroke: estSombre ? '#334155' : '#cbd5e1',
                  strokeWidth: 1.5,
                  strokeDasharray: '4 4',
                }}
                content={({ active, payload }) => {
                  if (active && payload && payload.length) {
                    const point = payload[0].payload as PointGraphique;
                    return (
                      <div className="rounded-xl border border-slate-800 bg-slate-900/95 dark:bg-slate-950/95 text-white p-3.5 shadow-2xl backdrop-blur-md min-w-[170px] space-y-1.5 animate-in fade-in zoom-in-95 duration-150">
                        <div className="flex items-center justify-between border-b border-slate-800 pb-1.5 mb-1.5">
                          <span className="text-xs font-semibold text-slate-300">
                            {point.periode} 2026
                          </span>
                          {point.tauxRealisation !== undefined && (
                            <span
                              className={cn(
                                'text-[11px] font-bold px-1.5 py-0.5 rounded-full',
                                point.tauxRealisation >= 100
                                  ? 'bg-emerald-500/20 text-emerald-400'
                                  : 'bg-amber-500/20 text-amber-400'
                              )}
                            >
                              {point.tauxRealisation}%
                            </span>
                          )}
                        </div>

                        <div>
                          <p className="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                            Cotisations perçues
                          </p>
                          <p className="text-sm font-extrabold text-white">
                            {formaterMontant(point.cotisationsReelles)}
                          </p>
                        </div>

                        <div className="pt-1 text-[11px] text-slate-400 flex items-center justify-between border-t border-slate-800/80">
                          <span>Objectif cible :</span>
                          <span className="font-semibold text-slate-300">
                            {formaterMontant(point.objectifBenchmark)}
                          </span>
                        </div>
                      </div>
                    );
                  }
                  return null;
                }}
              />

              {/* Ligne d'objectif / Benchmark en pointillé (exactement comme sur le modèle Dribbble) */}
              <ReferenceLine
                y={valeurBenchmark}
                stroke="#f97316"
                strokeDasharray="4 4"
                strokeWidth={1.5}
                strokeOpacity={0.8}
              />

              {/* Courbe principale fluide */}
              <Area
                type="monotone"
                dataKey="cotisationsReelles"
                stroke="#059669"
                strokeWidth={3}
                fillOpacity={1}
                fill="url(#degradeTontineModerne)"
                activeDot={{
                  r: 6,
                  fill: '#059669',
                  stroke: '#ffffff',
                  strokeWidth: 3,
                  className: 'drop-shadow-md',
                }}
                name="Cotisations réelles"
              />
            </AreaChart>
          </ResponsiveContainer>
        )}
      </div>

      {/* Légende horizontale en bas (modèle Dribbble) */}
      <div className="mt-4 pt-4 border-t border-slate-100 dark:border-sombre-bordure flex flex-wrap items-center justify-between gap-4 text-xs">
        <div className="flex items-center gap-6">
          {/* Indicateur Cotisations */}
          <div className="flex items-center gap-2">
            <span className="w-3 h-3 rounded bg-emerald-600 dark:bg-emerald-500" />
            <span className="font-medium text-slate-600 dark:text-slate-300">
              Cotisations collectées
            </span>
          </div>

          {/* Indicateur Objectif */}
          <div className="flex items-center gap-2">
            <span className="w-4 h-0.5 border-t-2 border-dashed border-orange-500" />
            <span className="font-medium text-slate-600 dark:text-slate-300">
              Objectif mensuel ({formaterMontant(valeurBenchmark)})
            </span>
          </div>
        </div>

        <div className="flex items-center gap-1.5 text-slate-400 text-[11px]">
          <Info className="w-3.5 h-3.5" />
          <span>Survolez la courbe pour voir le détail de chaque séance</span>
        </div>
      </div>
    </div>
  );
};
