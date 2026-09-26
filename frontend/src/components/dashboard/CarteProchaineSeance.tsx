import React from 'react';
import { Carte } from '../ui/Carte';
import { Seance, Cycle } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { Calendar, Clock, MapPin, ArrowRight } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Bouton } from '../ui/Bouton';
import { Badge } from '../ui/Badge';

interface PropsCarteProchaineSeance {
  seance?: Seance | null;
  cycleActif?: Cycle | null;
}

export const CarteProchaineSeance: React.FC<PropsCarteProchaineSeance> = ({
  seance,
  cycleActif,
}) => {
  if (!seance) {
    return (
      <Carte className="flex flex-col items-center justify-center p-8 text-center space-y-3">
        <div className="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-sombre-carte text-slate-400 flex items-center justify-center">
          <Calendar className="w-6 h-6" />
        </div>
        <div className="space-y-1">
          <h4 className="text-sm font-bold text-slate-800 dark:text-white">Aucune séance planifiée</h4>
          <p className="text-xs text-slate-500 dark:text-slate-400 max-w-xs">
            Le bureau n'a pas encore fixé la date de la prochaine rencontre.
          </p>
        </div>
      </Carte>
    );
  }

  const dateSeance = new Date(seance.date_seance);
  const auj = new Date();
  const diffJours = Math.ceil((dateSeance.getTime() - auj.getTime()) / (1000 * 60 * 60 * 24));

  return (
    <Carte className="relative overflow-hidden bg-gradient-to-br from-primaire-600 via-primaire-700 to-emerald-900 text-white border-0 shadow-lg shadow-primaire-600/20">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-white/10">
        <div className="space-y-1">
          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/20 text-white backdrop-blur">
            <Clock className="w-3.5 h-3.5" />
            {diffJours > 0 ? `Dans ${diffJours} jour${diffJours > 1 ? 's' : ''}` : "Aujourd'hui"}
          </span>
          <h3 className="text-xl font-black tracking-tight text-white">
            Prochaine Séance
          </h3>
          <p className="text-xs text-primaire-100">
            {cycleActif?.nom || 'Cycle en cours'}
          </p>
        </div>

        <div className="text-right sm:text-right">
          <p className="text-xs text-primaire-200">Date prévue</p>
          <p className="text-lg font-bold text-white">{formaterDate(seance.date_seance)}</p>
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4 py-4 text-xs">
        <div>
          <span className="text-primaire-200 block text-[11px]">Statut</span>
          <span className="font-semibold text-white capitalize">{seance.statut}</span>
        </div>
        <div>
          <span className="text-primaire-200 block text-[11px]">Montant Part</span>
          <span className="font-semibold text-white">{formaterMontant(cycleActif?.montant_part)}</span>
        </div>
      </div>

      <div className="pt-2">
        <Link to={`/seances/${seance.id}`}>
          <Bouton
            variante="contour"
            className="w-full justify-center bg-white/10 hover:bg-white/20 text-white border-white/20 backdrop-blur"
            icone={<ArrowRight className="w-4 h-4 ml-1" />}
          >
            Accéder à la Séance
          </Bouton>
        </Link>
      </div>
    </Carte>
  );
};
