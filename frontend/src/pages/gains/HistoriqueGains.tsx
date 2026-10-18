import React, { useEffect, useState, useMemo } from 'react';
import { Carte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { Alerte } from '../../components/ui/Alerte';
import { BarreFiltresTableau, OptionFiltre } from '../../components/ui/BarreFiltresTableau';
import { SqueletteTableau } from '../../components/ui/SqueletteChargement';
import api from '../../lib/api';
import { PaiementLot } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { PiggyBank, Check, Clock } from 'lucide-react';

export const HistoriqueGains: React.FC = () => {
  const [gains, setGains] = useState<PaiementLot[]>([]);
  const [chargement, setChargement] = useState(true);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  // Filtres
  const [recherche, setRecherche] = useState('');
  const [filtreStatut, setFiltreStatut] = useState<string>('tous');

  const chargerGains = async () => {
    setChargement(true);
    try {
      const rep = await api.get<{ succes: boolean; gains: PaiementLot[] }>('/mes-gains');
      if (rep.data.succes) setGains(rep.data.gains);
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerGains();
  }, []);

  const confirmerReception = async (id: number) => {
    try {
      const rep = await api.put(`/gains/${id}/confirmer`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerGains();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors de la confirmation.' });
    }
  };

  // Filtrage combiné
  const gainsFiltres = useMemo(() => {
    return gains.filter((g) => {
      const matchRecherche =
        !recherche ||
        (g.cycle?.nom && g.cycle.nom.toLowerCase().includes(recherche.toLowerCase())) ||
        String(g.montant).includes(recherche);

      const matchStatut =
        filtreStatut === 'tous' || g.statut === filtreStatut;

      return matchRecherche && matchStatut;
    });
  }, [gains, recherche, filtreStatut]);

  const optionsFiltres: OptionFiltre[] = [
    { cle: 'tous', label: 'Tous les lots', compte: gains.length },
    { cle: 'confirme', label: 'Confirmés', compte: gains.filter((g) => g.statut === 'confirme').length },
    { cle: 'en_attente', label: 'En attente', compte: gains.filter((g) => g.statut === 'en_attente').length },
  ];

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div>
        <h2 className="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
          Mes Gains de Tontine (Lots Reçus)
        </h2>
        <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Historique des cagnottes de tontine qui vous ont été versées lors de vos tours de passage
        </p>
      </div>

      {message && (
        <Alerte variante={message.type === 'succes' ? 'succes' : 'danger'}>
          {message.texte}
        </Alerte>
      )}

      {/* Barre de filtres et recherche responsive */}
      <BarreFiltresTableau
        termeRecherche={recherche}
        surChangementRecherche={setRecherche}
        placeholder="Rechercher par cycle, montant..."
        optionsFiltres={optionsFiltres}
        filtreActif={filtreStatut}
        surChangementFiltre={setFiltreStatut}
        totalResultats={gainsFiltres.length}
        libelleResultats="lot(s) trouvé(s)"
      />

      <Carte className="p-0 overflow-hidden border border-slate-200/80 dark:border-sombre-bordure">
        {chargement ? (
          <div className="p-6">
            <SqueletteTableau lignes={4} />
          </div>
        ) : (
          <div className="overflow-x-auto">
            {gainsFiltres.length === 0 ? (
              <div className="text-center py-12 text-xs text-slate-400 dark:text-slate-500">
                Aucun versement de lot trouvé pour vos critères.
              </div>
            ) : (
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-100 dark:border-sombre-bordure/60 text-slate-400 dark:text-slate-500 font-semibold tracking-wider uppercase text-[11px] bg-slate-50/50 dark:bg-sombre-surface/50">
                    <th className="py-3.5 px-4 sm:px-6">Date de Paiement</th>
                    <th className="py-3.5 px-4">Cycle</th>
                    <th className="py-3.5 px-4 text-right">Montant Perçu</th>
                    <th className="py-3.5 px-4">Statut</th>
                    <th className="py-3.5 px-4 sm:px-6 text-right">Action</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure/40">
                  {gainsFiltres.map((gain) => (
                    <tr
                      key={gain.id}
                      className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/40 transition-colors"
                    >
                      <td className="py-4 px-4 sm:px-6 font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                        {formaterDate(gain.date_paiement)}
                      </td>
                      <td className="py-4 px-4 text-slate-600 dark:text-slate-300">
                        {gain.cycle?.nom || 'Cycle Actif'}
                      </td>
                      {/* Montant neutre, net et élégant sans vert criard */}
                      <td className="py-4 px-4 text-right font-bold text-slate-900 dark:text-white tabular-nums text-sm whitespace-nowrap">
                        {formaterMontant(gain.montant)}
                      </td>
                      <td className="py-4 px-4 whitespace-nowrap">
                        <Badge variante={gain.statut === 'confirme' ? 'succes' : 'alerte'}>
                          {gain.statut === 'confirme' ? 'Reçu & Confirmé' : 'En attente confirmation'}
                        </Badge>
                      </td>
                      <td className="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                        {gain.statut === 'en_attente' && (
                          <Bouton
                            variante="primaire"
                            taille="sm"
                            onClick={() => confirmerReception(gain.id)}
                            icone={<Check className="w-3.5 h-3.5 mr-1" />}
                          >
                            Confirmer Réception
                          </Bouton>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        )}
      </Carte>
    </div>
  );
};
