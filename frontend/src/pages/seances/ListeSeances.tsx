import React, { useEffect, useState } from 'react';
import { Carte, EnteteCarte, TitreCarte, SousTitreCarte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Alerte } from '../../components/ui/Alerte';
import api from '../../lib/api';
import { Seance, Cycle } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { useAuthStore } from '../../stores/authStore';
import { Calendar, Plus, ArrowRight, FileText, CheckCircle2, AlertCircle, X } from 'lucide-react';
import { Link } from 'react-router-dom';

export const ListeSeances: React.FC = () => {
  const { estBureau } = useAuthStore();
  const [seances, setSeances] = useState<Seance[]>([]);
  const [cycles, setCycles] = useState<Cycle[]>([]);
  const [chargement, setChargement] = useState(true);
  const [modalOuvert, setModalOuvert] = useState(false);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  const [dateSeance, setDateSeance] = useState('');
  const [cycleId, setCycleId] = useState('');

  const chargerSeances = async () => {
    setChargement(true);
    try {
      const [repSeances, repCycles] = await Promise.all([
        api.get<{ succes: boolean; seances: Seance[] }>('/seances'),
        api.get<{ succes: boolean; cycles: Cycle[] }>('/cycles'),
      ]);

      if (repSeances.data.succes) setSeances(repSeances.data.seances);
      if (repCycles.data.succes) {
        setCycles(repCycles.data.cycles);
        const actif = repCycles.data.cycles.find((c) => c.est_actif);
        if (actif) setCycleId(String(actif.id));
      }
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerSeances();
  }, []);

  const planifierSeance = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const rep = await api.post('/seances', {
        cycle_id: cycleId,
        date_seance: dateSeance,
        statut: 'ouverte',
      });

      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: 'Séance planifiée avec succès.' });
        setModalOuvert(false);
        setDateSeance('');
        chargerSeances();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors de la planification.' });
    }
  };

  const badgesVersement = {
    non_verse: { variante: 'neutre' as const, label: 'Non versé en banque' },
    en_attente: { variante: 'alerte' as const, label: 'Reçu en vérification' },
    valide: { variante: 'succes' as const, label: 'Versement validé' },
    rejete: { variante: 'danger' as const, label: 'Versement rejeté' },
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            Séances & Réunions
          </h2>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Planification des réunions de tontine et tenue de la caisse du jour
          </p>
        </div>

        {estBureau && (
          <Bouton
            variante="primaire"
            onClick={() => setModalOuvert(true)}
            icone={<Plus className="w-4 h-4 mr-1" />}
          >
            Planifier une Séance
          </Bouton>
        )}
      </div>

      {message && (
        <Alerte variante={message.type === 'succes' ? 'succes' : 'danger'}>
          {message.texte}
        </Alerte>
      )}

      {/* Modal de planification */}
      {modalOuvert && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in">
          <div className="w-full max-w-md bg-white dark:bg-sombre-surface rounded-3xl p-6 shadow-2xl border border-slate-100 dark:border-sombre-bordure space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-sombre-bordure">
              <h3 className="text-base font-bold text-slate-900 dark:text-white">Planifier une Séance</h3>
              <button onClick={() => setModalOuvert(false)} className="p-1 text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={planifierSeance} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                  Cycle associé
                </label>
                <select
                  value={cycleId}
                  onChange={(e) => setCycleId(e.target.value)}
                  className="block w-full rounded-xl border border-slate-300 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2.5 px-3 text-sm text-slate-900 dark:text-white"
                  required
                >
                  {cycles.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.nom} {c.est_actif ? '(Actif)' : ''}
                    </option>
                  ))}
                </select>
              </div>

              <ChampTexte
                etiquette="Date de la réunion"
                type="date"
                required
                value={dateSeance}
                onChange={(e) => setDateSeance(e.target.value)}
              />

              <div className="flex gap-3 pt-2">
                <Bouton type="button" variante="contour" onClick={() => setModalOuvert(false)} className="flex-1 justify-center">
                  Annuler
                </Bouton>
                <Bouton type="submit" variante="primaire" className="flex-1 justify-center">
                  Confirmer
                </Bouton>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Liste des séances */}
      <Carte>
        <div className="overflow-x-auto">
          {seances.length === 0 ? (
            <div className="text-center py-12 text-xs text-slate-400">
              Aucune séance enregistrée pour le moment.
            </div>
          ) : (
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-slate-100 dark:border-sombre-bordure text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                  <th className="pb-3 px-3">Date</th>
                  <th className="pb-3 px-3">Cycle</th>
                  <th className="pb-3 px-3">Statut</th>
                  <th className="pb-3 px-3 text-right">Caisse Encaissée</th>
                  <th className="pb-3 px-3">Versement Banque</th>
                  <th className="pb-3 px-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure">
                {seances.map((seance) => {
                  const infoVersement = badgesVersement[seance.etat_versement] || {
                    variante: 'neutre' as const,
                    label: seance.etat_versement,
                  };

                  return (
                    <tr key={seance.id} className="hover:bg-slate-50/50 dark:hover:bg-sombre-carte/50 transition-colors">
                      <td className="py-3.5 px-3 font-bold text-slate-900 dark:text-white">
                        {formaterDate(seance.date_seance)}
                      </td>
                      <td className="py-3.5 px-3 text-slate-600 dark:text-slate-300">
                        {seance.cycle?.nom || '-'}
                      </td>
                      <td className="py-3.5 px-3">
                        <Badge variante={seance.statut === 'ouverte' ? 'succes' : 'neutre'}>
                          {seance.statut}
                        </Badge>
                      </td>
                      <td className="py-3.5 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                        {formaterMontant(seance.total_encaisse)}
                      </td>
                      <td className="py-3.5 px-3">
                        <Badge variante={infoVersement.variante}>{infoVersement.label}</Badge>
                      </td>
                      <td className="py-3.5 px-3 text-right whitespace-nowrap space-x-2">
                        <Link to={`/seances/${seance.id}`}>
                          <Bouton variante="primaire" taille="sm" icone={<ArrowRight className="w-3.5 h-3.5 mr-1" />}>
                            Ouvrir
                          </Bouton>
                        </Link>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          )}
        </div>
      </Carte>
    </div>
  );
};
