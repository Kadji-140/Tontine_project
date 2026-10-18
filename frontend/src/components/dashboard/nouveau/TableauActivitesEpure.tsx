import React from 'react';
import { cn } from '../../../lib/utils';
import { Link } from 'react-router-dom';

/**
 * Interface pour chaque ligne d'indicateur du tableau épuré
 */
interface LigneIndicateur {
  id: string | number;
  titreType: string;
  sousTitreType: string;
  badgeTexte: string;
  badgeType: 'rouge' | 'orange' | 'vert';
  valeurMoisEnCours: string;
  valeurMoisPrecedent: string;
}

/**
 * Données réelles ou réalistes adaptées 100% à la vie d'une tontine
 */
const donneesTontineParDefaut: LigneIndicateur[] = [
  {
    id: 1,
    titreType: 'Cotisations Tontine & Épargne',
    sousTitreType: 'Épargne collective par lot',
    badgeTexte: 'Taux : 94%',
    badgeType: 'vert',
    valeurMoisEnCours: '42 cotisations validées (Séance #07)',
    valeurMoisPrecedent: '51 cotisations (Séance #06)',
  },
  {
    id: 2,
    titreType: 'Jean-Marc Ndongo',
    sousTitreType: 'Membre Adhérent • Prêt Scolaire',
    badgeTexte: 'Échéance : -4 jours',
    badgeType: 'orange',
    valeurMoisEnCours: '220 000 FCFA (Capital + Intérêts)',
    valeurMoisPrecedent: '100 000 FCFA remboursés',
  },
  {
    id: 3,
    titreType: 'Suzanne Ngo',
    sousTitreType: 'Membre Adhérent • Caisse de Secours',
    badgeTexte: 'À jour : 100%',
    badgeType: 'vert',
    valeurMoisEnCours: '30 000 FCFA versés',
    valeurMoisPrecedent: '30 000 FCFA versés',
  },
  {
    id: 4,
    titreType: 'Alain Foko',
    sousTitreType: 'Membre Adhérent • Sanction Retard',
    badgeTexte: 'Pénalité : Impayée',
    badgeType: 'rouge',
    valeurMoisEnCours: '5 000 FCFA (Séance #06)',
    valeurMoisPrecedent: '0 FCFA',
  },
];

/**
 * Composant TableauActivitesEpure :
 * Traduction 1:1 de la section "Sales insights for 30 days" adaptée à la Tontine.
 */
export const TableauActivitesEpure: React.FC = () => {
  return (
    <div className="bg-white dark:bg-sombre-carte rounded-2xl border border-slate-200/70 dark:border-sombre-bordure shadow-[0_1px_3px_rgba(0,0,0,0.03)] p-6 sm:p-7">
      {/* En-tête : Titre à gauche, bouton "Toutes les séances" à droite */}
      <div className="flex items-center justify-between mb-6">
        <div>
          <h3 className="text-lg font-bold text-slate-900 dark:text-white tracking-tight">
            Indicateurs & Activités Récentes (30 jours)
          </h3>
          <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
            Suivi des cotisations perçues, remboursements et respect des délais
          </p>
        </div>

        <Link
          to="/seances"
          className="px-3.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-sombre-survol transition-colors shadow-2xs"
        >
          Toutes les séances
        </Link>
      </div>

      {/* Tableau minimaliste */}
      <div className="overflow-x-auto">
        <table className="w-full text-left">
          <thead>
            <tr className="text-xs text-slate-400 dark:text-slate-500 font-normal border-b border-slate-100 dark:border-sombre-bordure/60">
              <th className="pb-3.5 font-medium">Opération & Membre</th>
              <th className="pb-3.5 font-medium">Indicateur / Statut</th>
              <th className="pb-3.5 font-medium">Séance en cours</th>
              <th className="pb-3.5 font-medium">Historique / Séance précédente</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure/40">
            {donneesTontineParDefaut.map((ligne) => (
              <tr key={ligne.id} className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/40 transition-colors">
                {/* Colonne 1 : Type & Membre */}
                <td className="py-4 pr-4 align-top">
                  <p className="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                    {ligne.titreType}
                  </p>
                  <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                    {ligne.sousTitreType}
                  </p>
                </td>

                {/* Colonne 2 : Badge Pilule d'Insight pastel */}
                <td className="py-4 px-4 align-top whitespace-nowrap">
                  <span
                    className={cn(
                      'inline-block px-2.5 py-1 rounded-md text-xs font-semibold tracking-tight',
                      ligne.badgeType === 'rouge' &&
                        'bg-[#fff1f2] text-[#e11d48] dark:bg-rose-950/60 dark:text-rose-300',
                      ligne.badgeType === 'orange' &&
                        'bg-[#fff7ed] text-[#ea580c] dark:bg-amber-950/60 dark:text-amber-300',
                      ligne.badgeType === 'vert' &&
                        'bg-[#ecfdf5] text-[#059669] dark:bg-emerald-950/60 dark:text-emerald-300'
                    )}
                  >
                    {ligne.badgeTexte}
                  </span>
                </td>

                {/* Colonne 3 : Ce mois */}
                <td className="py-4 px-4 align-top text-sm font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                  {ligne.valeurMoisEnCours}
                </td>

                {/* Colonne 4 : Mois précédent */}
                <td className="py-4 pl-4 align-top text-sm font-normal text-slate-600 dark:text-slate-300 whitespace-nowrap">
                  {ligne.valeurMoisPrecedent}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};
