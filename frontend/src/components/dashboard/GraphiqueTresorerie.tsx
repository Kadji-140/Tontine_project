import React, { useEffect, useState } from 'react';
import { Carte, EnteteCarte, TitreCarte, SousTitreCarte } from '../ui/Carte';
import { AreaChart, Area, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from 'recharts';
import api from '../../lib/api';
import { formaterMontant, cn } from '../../lib/utils';
import { useTheme } from '../../hooks/useTheme';
import { TrendingUp, Loader2 } from 'lucide-react';

export const GraphiqueTresorerie: React.FC = () => {
  const [filtre, setFiltre] = useState<'today' | 'month' | 'year'>('month');
  const [chargement, setChargement] = useState(false);
  const [donnees, setDonnees] = useState<{ date: string; encaisse: number; depenses: number; banque: number }[]>([]);
  const { estSombre } = useTheme();

  useEffect(() => {
    const chargerDonnees = async () => {
      setChargement(true);
      try {
        const reponse = await api.get<{
          succes: boolean;
          labels: string[];
          encaisse: number[];
          depenses: number[];
          banque: number[];
        }>(`/dashboard/graphique?filtre=${filtre}`);

        if (reponse.data.succes) {
          const formatees = reponse.data.labels.map((label, index) => ({
            date: label,
            encaisse: reponse.data.encaisse[index] || 0,
            depenses: reponse.data.depenses[index] || 0,
            banque: reponse.data.banque[index] || 0,
          }));
          setDonnees(formatees);
        }
      } catch {
        // Ignorer
      } finally {
        setChargement(false);
      }
    };

    chargerDonnees();
  }, [filtre]);

  const filtres = [
    { cle: 'today' as const, label: '7 jours' },
    { cle: 'month' as const, label: 'Ce mois' },
    { cle: 'year' as const, label: 'Année' },
  ];

  return (
    <Carte>
      <EnteteCarte className="flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <div className="flex items-center gap-2">
            <TrendingUp className="w-5 h-5 text-primaire-600 dark:text-primaire-400" />
            <TitreCarte>Évolution de la Trésorerie</TitreCarte>
          </div>
          <SousTitreCarte>Flux des encaissements, décaissements et réserve en banque</SousTitreCarte>
        </div>

        <div className="flex items-center p-1 bg-slate-100 dark:bg-sombre-carte rounded-xl border border-slate-200 dark:border-sombre-bordure">
          {filtres.map((f) => (
            <button
              key={f.cle}
              onClick={() => setFiltre(f.cle)}
              className={cn(
                'px-3 py-1 text-xs font-semibold rounded-lg transition-all',
                filtre === f.cle
                  ? 'bg-white dark:bg-sombre-surface text-primaire-600 dark:text-primaire-400 shadow-sm'
                  : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'
              )}
            >
              {f.label}
            </button>
          ))}
        </div>
      </EnteteCarte>

      <div className="h-72 w-full pt-4">
        {chargement ? (
          <div className="h-full flex items-center justify-center text-slate-400">
            <Loader2 className="w-6 h-6 animate-spin mr-2" />
            <span className="text-xs">Chargement du graphique...</span>
          </div>
        ) : donnees.length === 0 ? (
          <div className="h-full flex items-center justify-center text-slate-400 text-xs">
            Aucune donnée de trésorerie disponible pour cette période.
          </div>
        ) : (
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={donnees} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
              <defs>
                <linearGradient id="degradeEncaisse" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#10b981" stopOpacity={0.4} />
                  <stop offset="95%" stopColor="#10b981" stopOpacity={0.0} />
                </linearGradient>
                <linearGradient id="degradeDepenses" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#f43f5e" stopOpacity={0.4} />
                  <stop offset="95%" stopColor="#f43f5e" stopOpacity={0.0} />
                </linearGradient>
                <linearGradient id="degradeBanque" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#6366f1" stopOpacity={0.4} />
                  <stop offset="95%" stopColor="#6366f1" stopOpacity={0.0} />
                </linearGradient>
              </defs>

              <CartesianGrid
                strokeDasharray="3 3"
                stroke={estSombre ? '#1f293d' : '#f1f5f9'}
                vertical={false}
              />

              <XAxis
                dataKey="date"
                stroke={estSombre ? '#64748b' : '#94a3b8'}
                fontSize={11}
                tickLine={false}
                axisLine={false}
              />

              <YAxis
                stroke={estSombre ? '#64748b' : '#94a3b8'}
                fontSize={11}
                tickLine={false}
                axisLine={false}
                tickFormatter={(v) => `${(v / 1000).toFixed(0)}k`}
              />

              <Tooltip
                content={({ active, payload, label }) => {
                  if (active && payload && payload.length) {
                    return (
                      <div className="rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white/95 dark:bg-sombre-carte/95 backdrop-blur p-3 shadow-xl text-xs space-y-1.5">
                        <p className="font-bold text-slate-900 dark:text-white pb-1 border-b border-slate-100 dark:border-sombre-bordure">
                          {label}
                        </p>
                        <p className="text-emerald-600 dark:text-emerald-400 font-medium">
                          Encaissé : {formaterMontant(Number(payload[0]?.value))}
                        </p>
                        <p className="text-rose-600 dark:text-rose-400 font-medium">
                          Décaissements : {formaterMontant(Number(payload[1]?.value))}
                        </p>
                        <p className="text-indigo-600 dark:text-indigo-400 font-medium">
                          Solde cumulé : {formaterMontant(Number(payload[2]?.value))}
                        </p>
                      </div>
                    );
                  }
                  return null;
                }}
              />

              <Area
                type="monotone"
                dataKey="encaisse"
                stroke="#10b981"
                strokeWidth={2}
                fillOpacity={1}
                fill="url(#degradeEncaisse)"
                name="Encaissé"
              />
              <Area
                type="monotone"
                dataKey="depenses"
                stroke="#f43f5e"
                strokeWidth={2}
                fillOpacity={1}
                fill="url(#degradeDepenses)"
                name="Sorties"
              />
              <Area
                type="monotone"
                dataKey="banque"
                stroke="#6366f1"
                strokeWidth={2}
                fillOpacity={1}
                fill="url(#degradeBanque)"
                name="Banque"
              />
            </AreaChart>
          </ResponsiveContainer>
        )}
      </div>
    </Carte>
  );
};
