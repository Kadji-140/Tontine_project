import React from 'react';
import { Seance, Cycle } from '../../../types';
import { formaterDate, formaterMontant, cn } from '../../../lib/utils';
import { Calendar, Clock, MapPin, Award, CheckCircle, ArrowRight, Sparkles } from 'lucide-react';
import { Link } from 'react-router-dom';

/**
 * Propriétés du composant CarteProchaineReunion
 */
interface ProprietesCarteProchaineReunion {
  seance?: Seance | null;
  cycleActif?: Cycle | null;
}

/**
 * Fiche informative moderne présentant la prochaine réunion de tontine,
 * le décompte des jours, ainsi que le membre bénéficiaire du tirage.
 */
export const CarteProchaineReunion: React.FC<ProprietesCarteProchaineReunion> = ({
  seance,
  cycleActif,
}) => {
  // Calcul approximatif du nombre de jours restants
  const dateSeance = seance?.date_seance ? new Date(seance.date_seance) : new Date(Date.now() + 86400000 * 5);
  const maintenant = new Date();
  const diffJours = Math.max(0, Math.ceil((dateSeance.getTime() - maintenant.getTime()) / (1000 * 3600 * 24)));

  const estOuverte = seance?.statut === 'ouverte';

  return (
    <div className="relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-sombre-bordure bg-white dark:bg-sombre-carte p-6 shadow-[0_2px_12px_-4px_rgba(0,0,0,0.04)] flex flex-col justify-between">
      <div>
        {/* En-tête : Badge statut et compte à rebours */}
        <div className="flex items-center justify-between gap-2 mb-4">
          <span
            className={cn(
              'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold tracking-tight',
              estOuverte
                ? 'bg-emerald-500 text-white animate-pulse'
                : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/50'
            )}
          >
            <Sparkles className="w-3 h-3" />
            <span>{estOuverte ? 'Séance en direct' : 'Prochaine Séance'}</span>
          </span>

          <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
            {diffJours === 0 ? "Aujourd'hui" : `Dans ${diffJours} jours`}
          </span>
        </div>

        {/* Date et Heure */}
        <div className="space-y-1 mb-5">
          <h4 className="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            <Calendar className="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
            <span>{seance?.date_seance ? formaterDate(seance.date_seance) : 'Dimanche prochain à 15h00'}</span>
          </h4>
          <p className="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 pl-7">
            <Clock className="w-3.5 h-3.5" />
            <span>{cycleActif ? `Cycle : ${cycleActif.nom}` : 'Cycle Actif Annuel 2026'}</span>
          </p>
        </div>

        {/* Bloc Bénéficiaire du tirage du jour */}
        <div className="rounded-xl bg-slate-50 dark:bg-sombre-surface/70 border border-slate-100 dark:border-sombre-bordure p-4 mb-4">
          <div className="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs font-medium mb-1.5">
            <Award className="w-4 h-4 text-amber-500" />
            <span>Bénéficiaire du tirage prévu :</span>
          </div>
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2.5">
              <div className="w-7 h-7 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-bold text-xs flex items-center justify-center">
                AK
              </div>
              <span className="text-sm font-bold text-slate-900 dark:text-white">
                Mme Alice Kouam (Lot #04)
              </span>
            </div>
            <span className="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
              ~ 1 500 000 FCFA
            </span>
          </div>
        </div>
      </div>

      {/* Bouton d'action */}
      <div className="pt-2">
        <Link
          to={seance?.id ? `/seances/${seance.id}` : '/seances'}
          className="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 hover:shadow-emerald-600/30 transition-all duration-200"
        >
          <span>{estOuverte ? 'Entrer dans la séance' : 'Voir les détails de la séance'}</span>
          <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    </div>
  );
};
