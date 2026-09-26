import React from 'react';
import { Carte, EnteteCarte, TitreCarte, SousTitreCarte } from '../ui/Carte';
import { Cotisation } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { Badge } from '../ui/Badge';
import { ArrowDownLeft, Clock } from 'lucide-react';

interface PropsTableActivitesRecentes {
  activites?: Cotisation[];
}

export const TableActivitesRecentes: React.FC<PropsTableActivitesRecentes> = ({
  activites = [],
}) => {
  return (
    <Carte>
      <EnteteCarte>
        <div>
          <TitreCarte>Activités & Versements Récents</TitreCarte>
          <SousTitreCarte>Dernières cotisations enregistrées</SousTitreCarte>
        </div>
      </EnteteCarte>

      <div className="overflow-x-auto">
        {activites.length === 0 ? (
          <div className="text-center py-8 text-xs text-slate-400">
            Aucun versement enregistré pour le moment.
          </div>
        ) : (
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-slate-100 dark:border-sombre-bordure text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                <th className="pb-3 px-2">Membre</th>
                <th className="pb-3 px-2">Type</th>
                <th className="pb-3 px-2 text-right">Montant</th>
                <th className="pb-3 px-2 text-right">Date</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure">
              {activites.map((cotisation) => (
                <tr key={cotisation.id} className="hover:bg-slate-50/50 dark:hover:bg-sombre-carte/50 transition-colors">
                  <td className="py-3 px-2 font-medium text-slate-800 dark:text-slate-200">
                    <div className="flex items-center gap-2">
                      <div className="w-7 h-7 rounded-lg bg-primaire-50 dark:bg-primaire-950/40 text-primaire-600 dark:text-primaire-400 flex items-center justify-center font-bold text-[11px]">
                        {cotisation.user?.name?.charAt(0).toUpperCase() || 'M'}
                      </div>
                      <span className="truncate max-w-[120px] sm:max-w-none">{cotisation.user?.name || 'Membre'}</span>
                    </div>
                  </td>
                  <td className="py-3 px-2">
                    <Badge
                      variante={cotisation.type === 'tontine' ? 'primaire' : 'info'}
                      taille="sm"
                    >
                      {cotisation.type}
                    </Badge>
                  </td>
                  <td className="py-3 px-2 text-right font-bold text-slate-900 dark:text-white">
                    +{formaterMontant(cotisation.montant)}
                  </td>
                  <td className="py-3 px-2 text-right text-slate-400 dark:text-slate-500 text-[11px]">
                    {formaterDate(cotisation.created_at)}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </Carte>
  );
};
