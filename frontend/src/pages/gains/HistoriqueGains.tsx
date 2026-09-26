import React, { useEffect, useState } from 'react';
import { Carte, EnteteCarte, TitreCarte, SousTitreCarte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { Alerte } from '../../components/ui/Alerte';
import api from '../../lib/api';
import { PaiementLot } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { PiggyBank, Check, Clock } from 'lucide-react';

export const HistoriqueGains: React.FC = () => {
  const [gains, setGains] = useState<PaiementLot[]>([]);
  const [chargement, setChargement] = useState(true);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

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

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
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

      <Carte>
        <div className="overflow-x-auto">
          {gains.length === 0 ? (
            <div className="text-center py-12 text-xs text-slate-400">
              Vous n'avez pas encore reçu de lot de tontine sur ce cycle.
            </div>
          ) : (
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-slate-100 dark:border-sombre-bordure text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                  <th className="pb-3 px-3">Date</th>
                  <th className="pb-3 px-3">Cycle</th>
                  <th className="pb-3 px-3 text-right">Montant Perçu</th>
                  <th className="pb-3 px-3">Statut</th>
                  <th className="pb-3 px-3 text-right">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure">
                {gains.map((gain) => (
                  <tr key={gain.id} className="hover:bg-slate-50/50 dark:hover:bg-sombre-carte/50 transition-colors">
                    <td className="py-3.5 px-3 font-semibold text-slate-900 dark:text-white">
                      {formaterDate(gain.date_paiement)}
                    </td>
                    <td className="py-3.5 px-3 text-slate-600 dark:text-slate-300">
                      {gain.cycle?.nom || '-'}
                    </td>
                    <td className="py-3.5 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                      {formaterMontant(gain.montant)}
                    </td>
                    <td className="py-3.5 px-3">
                      <Badge variante={gain.statut === 'confirme' ? 'succes' : 'alerte'}>
                        {gain.statut === 'confirme' ? 'Reçu & Confirmé' : 'En attente de confirmation'}
                      </Badge>
                    </td>
                    <td className="py-3.5 px-3 text-right">
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
      </Carte>
    </div>
  );
};
