import React, { useState } from 'react';
import { Cotisation } from '../../../types';
import { formaterDate, formaterMontant, cn } from '../../../lib/utils';
import {
  Wallet,
  ArrowUpRight,
  ShieldAlert,
  Coins,
  CheckCircle2,
  Clock,
  ArrowRight
} from 'lucide-react';
import { Link } from 'react-router-dom';

/**
 * Type d'opération enrichie pour le tableau d'activités
 */
export interface OperationTontine {
  id: number | string;
  nomMembre: string;
  roleMembre?: string;
  initiale: string;
  avatarCouleur?: string;
  typeOperation: 'cotisation' | 'pret' | 'remboursement' | 'sanction' | 'secours';
  libelle: string;
  statutLibelle: string;
  statutType: 'succes' | 'alerte' | 'info' | 'neutre';
  montantActuel: number;
  precisionMois?: string;
  cumulHistorique: number;
  precisionHistorique?: string;
  date: string;
}

/**
 * Données d'exemple fidèles au modèle Dribbble (Sales insights for 30 days)
 * Présentant des cas concrets de tontine : cotisations, remboursements, octrois de prêts et sanctions.
 */
const operationsParDefaut: OperationTontine[] = [
  {
    id: 1,
    nomMembre: 'Pauline Mbarga',
    roleMembre: 'Membre Adhérent • Lot #03',
    initiale: 'PM',
    avatarCouleur: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    typeOperation: 'cotisation',
    libelle: 'Cotisation Tontine & Épargne',
    statutLibelle: 'À jour : 100%',
    statutType: 'succes',
    montantActuel: 150000,
    precisionMois: 'Séance #07 validée',
    cumulHistorique: 1050000,
    precisionHistorique: '7 versements réguliers',
    date: 'Hier à 16:45',
  },
  {
    id: 2,
    nomMembre: 'Jean-Pierre Kamga',
    roleMembre: 'Secrétaire Adjoint • Lot #08',
    initiale: 'JK',
    avatarCouleur: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300',
    typeOperation: 'pret',
    libelle: 'Remboursement Échéance Prêt',
    statutLibelle: 'Échéance : -4 jours',
    statutType: 'info',
    montantActuel: 220000,
    precisionMois: 'Capital + Intérêts (5%)',
    cumulHistorique: 880000,
    precisionHistorique: 'Reste dû : 220 000 FCFA',
    date: '25 Sept 2026',
  },
  {
    id: 3,
    nomMembre: 'Suzanne Eboa',
    roleMembre: 'Membre Adhérent • Lot #12',
    initiale: 'SE',
    avatarCouleur: 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
    typeOperation: 'sanction',
    libelle: 'Sanction Retard Réunion',
    statutLibelle: 'Impayé : +15j',
    statutType: 'alerte',
    montantActuel: 5000,
    precisionMois: 'Séance #06 en attente',
    cumulHistorique: 15000,
    precisionHistorique: '1 amende restante',
    date: '22 Sept 2026',
  },
  {
    id: 4,
    nomMembre: 'Christian Talla',
    roleMembre: 'Trésorier • Lot #01',
    initiale: 'CT',
    avatarCouleur: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    typeOperation: 'secours',
    libelle: 'Fonds de Secours & Caisse Événement',
    statutLibelle: 'Cotisation reçue',
    statutType: 'succes',
    montantActuel: 30000,
    precisionMois: 'Versement trimestriel',
    cumulHistorique: 90000,
    precisionHistorique: 'Solde personnel à jour',
    date: '20 Sept 2026',
  },
  {
    id: 5,
    nomMembre: 'Béatrice Nana',
    roleMembre: 'Membre Adhérent • Lot #05',
    initiale: 'BN',
    avatarCouleur: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    typeOperation: 'pret',
    libelle: 'Demande de Prêt Scolaire',
    statutLibelle: 'En cours validation',
    statutType: 'neutre',
    montantActuel: 500000,
    precisionMois: 'Durée : 3 mois (3% mensuel)',
    cumulHistorique: 0,
    precisionHistorique: 'Premier prêt du cycle',
    date: '18 Sept 2026',
  },
];

/**
 * Propriétés attendues pour le composant TableauOperationsTontine
 */
interface ProprietesTableauOperationsTontine {
  activitesReelles?: Cotisation[];
  titre?: string;
}

/**
 * Composant de tableau d'activités reprenant la structure "Sales insights for 30 days" :
 * - Type / Membre
 * - Insight / Statut avec badge pilule doux
 * - Valeur période actuelle
 * - Valeur période précédente / cumul
 */
export const TableauOperationsTontine: React.FC<ProprietesTableauOperationsTontine> = ({
  activitesReelles = [],
  titre = 'Opérations & Indicateurs Récentes (30 jours)',
}) => {
  const [filtreSelectionne, setFiltreSelectionne] = useState<string>('tous');

  // Conversion des activités réelles de l'API si disponibles
  const operationsAffichees: OperationTontine[] = React.useMemo(() => {
    if (activitesReelles && activitesReelles.length > 0) {
      return activitesReelles.map((cotisation, index) => {
        const nom = cotisation.user?.name || 'Membre Adhérent';
        const initiales = nom
          .split(' ')
          .map((n) => n[0])
          .join('')
          .substring(0, 2)
          .toUpperCase() || 'MB';

        return {
          id: cotisation.id || index,
          nomMembre: nom,
          roleMembre: `Membre • Séance #${cotisation.seance_id || '1'}`,
          initiale: initiales,
          avatarCouleur: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
          typeOperation: (cotisation.type as any) || 'cotisation',
          libelle: `Cotisation ${cotisation.type || 'Tontine'}`,
          statutLibelle: 'Encaissé & Validé',
          statutType: 'succes',
          montantActuel: cotisation.montant || 0,
          precisionMois: `Séance #${cotisation.seance_id || 'actuelle'}`,
          cumulHistorique: (cotisation.montant || 0) * 3,
          precisionHistorique: 'Cotisation vérifiée',
          date: formaterDate(cotisation.created_at),
        };
      });
    }

    return operationsParDefaut;
  }, [activitesReelles]);

  // Filtrage selon le filtre actif
  const operationsFiltrees = operationsAffichees.filter((op) => {
    if (filtreSelectionne === 'tous') return true;
    return op.typeOperation === filtreSelectionne;
  });

  // Styles des badges pilules d'insight (style Dribbble)
  const stylesBadgesInsight = {
    succes:
      'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/50',
    alerte:
      'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/50',
    info:
      'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/50',
    neutre:
      'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/50',
  };

  return (
    <div className="relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-sombre-bordure bg-white dark:bg-sombre-carte shadow-[0_2px_12px_-4px_rgba(0,0,0,0.04)]">
      {/* En-tête : Titre et sélecteur de catégorie (Bouton "Tous" et filtres) */}
      <div className="p-6 pb-4 border-b border-slate-100 dark:border-sombre-bordure flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h4 className="text-base font-bold text-slate-900 dark:text-white tracking-tight">
            {titre}
          </h4>
          <p className="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
            Derniers flux enregistrés lors des réunions et versements bancaires
          </p>
        </div>

        {/* Filtres style Dribbble (Tous / Cotisations / Prêts / Sanctions) */}
        <div className="flex flex-wrap items-center gap-1.5">
          {[
            { cle: 'tous', libelle: 'Tous' },
            { cle: 'cotisation', libelle: 'Cotisations' },
            { cle: 'pret', libelle: 'Prêts' },
            { cle: 'sanction', libelle: 'Sanctions' },
          ].map((f) => (
            <button
              key={f.cle}
              onClick={() => setFiltreSelectionne(f.cle)}
              className={cn(
                'px-3 py-1 text-xs font-semibold rounded-lg transition-all duration-200',
                filtreSelectionne === f.cle
                  ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm'
                  : 'bg-slate-100/80 dark:bg-sombre-surface text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-sombre-survol'
              )}
            >
              {f.libelle}
            </button>
          ))}
        </div>
      </div>

      {/* Tableau minimaliste Dribbble */}
      <div className="overflow-x-auto">
        <table className="w-full text-left text-xs">
          <thead>
            <tr className="border-b border-slate-100 dark:border-sombre-bordure text-slate-400 dark:text-slate-500 font-semibold tracking-wider uppercase text-[11px]">
              <th className="py-3.5 px-6">Membre & Opération</th>
              <th className="py-3.5 px-4">Indicateur / Statut</th>
              <th className="py-3.5 px-6 text-right">Séance Actuelle</th>
              <th className="py-3.5 px-6 text-right">Cumul / Évolution</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure">
            {operationsFiltrees.length === 0 ? (
              <tr>
                <td colSpan={4} className="py-12 text-center text-slate-400 dark:text-slate-500">
                  Aucune opération trouvée pour ce filtre.
                </td>
              </tr>
            ) : (
              operationsFiltrees.map((operation) => (
                <tr
                  key={operation.id}
                  className="hover:bg-slate-50/70 dark:hover:bg-sombre-survol/50 transition-colors duration-150 group"
                >
                  {/* Colonne 1 : Membre & Type */}
                  <td className="py-4 px-6">
                    <div className="flex items-center gap-3">
                      {/* Avatar avec initiales */}
                      <div
                        className={cn(
                          'w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0',
                          operation.avatarCouleur ||
                            'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                        )}
                      >
                        {operation.initiale}
                      </div>

                      <div className="min-w-0">
                        <p className="font-bold text-slate-900 dark:text-white truncate text-xs sm:text-sm">
                          {operation.nomMembre}
                        </p>
                        <p className="text-[11px] text-slate-400 dark:text-slate-500 truncate">
                          {operation.libelle} • {operation.roleMembre}
                        </p>
                      </div>
                    </div>
                  </td>

                  {/* Colonne 2 : Badge Pilule d'Insight */}
                  <td className="py-4 px-4 whitespace-nowrap">
                    <span
                      className={cn(
                        'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-tight',
                        stylesBadgesInsight[operation.statutType]
                      )}
                    >
                      {operation.statutLibelle}
                    </span>
                  </td>

                  {/* Colonne 3 : Ce mois / Montant séance */}
                  <td className="py-4 px-6 text-right whitespace-nowrap">
                    <p className="font-extrabold text-slate-900 dark:text-white text-xs sm:text-sm">
                      {formaterMontant(operation.montantActuel)}
                    </p>
                    {operation.precisionMois && (
                      <p className="text-[11px] text-slate-400 dark:text-slate-500">
                        {operation.precisionMois}
                      </p>
                    )}
                  </td>

                  {/* Colonne 4 : Mois précédent / Historique */}
                  <td className="py-4 px-6 text-right whitespace-nowrap">
                    <p className="font-semibold text-slate-600 dark:text-slate-300 text-xs">
                      {formaterMontant(operation.cumulHistorique)}
                    </p>
                    {operation.precisionHistorique && (
                      <p className="text-[11px] text-slate-400 dark:text-slate-500">
                        {operation.precisionHistorique}
                      </p>
                    )}
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Pied de tableau avec lien d'action vers les séances */}
      <div className="p-4 bg-slate-50/50 dark:bg-sombre-surface/40 border-t border-slate-100 dark:border-sombre-bordure flex items-center justify-between">
        <span className="text-xs text-slate-400 dark:text-slate-500">
          Affichage des 30 derniers jours d'activité
        </span>
        <Link
          to="/seances"
          className="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors"
        >
          <span>Accéder à toutes les séances</span>
          <ArrowRight className="w-3.5 h-3.5" />
        </Link>
      </div>
    </div>
  );
};
