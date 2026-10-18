import React, { useEffect, useState, useMemo } from 'react';
import { Carte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Alerte } from '../../components/ui/Alerte';
import { BarreFiltresTableau, OptionFiltre } from '../../components/ui/BarreFiltresTableau';
import { SqueletteTableau } from '../../components/ui/SqueletteChargement';
import api from '../../lib/api';
import { Seance, Cycle } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { useAuthStore } from '../../stores/authStore';
import { Calendar, Plus, ArrowRight, X } from 'lucide-react';
import { Link, useLocation } from 'react-router-dom';

export const ListeSeances: React.FC = () => {
  const { estBureau } = useAuthStore();
  const location = useLocation();
  const [seances, setSeances] = useState<Seance[]>([]);
  const [cycles, setCycles] = useState<Cycle[]>([]);
  const [chargement, setChargement] = useState(true);
  const [modalOuvert, setModalOuvert] = useState(false);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  const [dateSeance, setDateSeance] = useState('');
  const [cycleId, setCycleId] = useState('');

  // Filtres
  const [recherche, setRecherche] = useState('');
  const [filtreStatut, setFiltreStatut] = useState<string>('tous');

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

  // Filtrage combiné
  const seancesFiltrees = useMemo(() => {
    return seances.filter((s) => {
      const matchRecherche =
        !recherche ||
        (s.cycle?.nom && s.cycle.nom.toLowerCase().includes(recherche.toLowerCase())) ||
        (s.date_seance && s.date_seance.includes(recherche));

      const matchStatut =
        filtreStatut === 'tous' || s.statut === filtreStatut;

      return matchRecherche && matchStatut;
    });
  }, [seances, recherche, filtreStatut]);

  const optionsFiltres: OptionFiltre[] = [
    { cle: 'tous', label: 'Toutes les séances', compte: seances.length },
    { cle: 'ouverte', label: 'Ouvertes', compte: seances.filter((s) => s.statut === 'ouverte').length },
    { cle: 'fermee', label: 'Clôturées', compte: seances.filter((s) => s.statut === 'fermee').length },
  ];

  const badgesVersement = {
    non_verse: { variante: 'neutre' as const, label: 'Non versé en banque' },
    en_attente: { variante: 'alerte' as const, label: 'Reçu en vérification' },
    valide: { variante: 'succes' as const, label: 'Versement validé' },
    rejete: { variante: 'danger' as const, label: 'Versement rejeté' },
  };

  const prefixRoute = location.pathname.startsWith('/moderne') ? '/moderne/seances' : '/seances';

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
            Séances & Réunions
          </h2>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Planification des réunions de tontine, enregistrement des présences et tenue de caisse
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

      {/* Barre de filtres et recherche responsive */}
      <BarreFiltresTableau
        termeRecherche={recherche}
        surChangementRecherche={setRecherche}
        placeholder="Rechercher par date, nom de cycle..."
        optionsFiltres={optionsFiltres}
        filtreActif={filtreStatut}
        surChangementFiltre={setFiltreStatut}
        totalResultats={seancesFiltrees.length}
        libelleResultats="séance(s) listée(s)"
      />

      {/* Modal de planification */}
      {modalOuvert && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in">
          <div className="w-full max-w-md bg-white dark:bg-sombre-surface rounded-2xl p-6 shadow-2xl border border-slate-200/80 dark:border-sombre-bordure space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-sombre-bordure">
              <h3 className="text-base font-bold text-slate-900 dark:text-white">Planifier une Séance</h3>
              <button onClick={() => setModalOuvert(false)} className="p-1 text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={planifierSeance} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                  Cycle associé
                </label>
                <select
                  value={cycleId}
                  onChange={(e) => setCycleId(e.target.value)}
                  className="block w-full rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2 px-3 text-xs text-slate-900 dark:text-white"
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
      <Carte className="p-0 overflow-hidden border border-slate-200/80 dark:border-sombre-bordure">
        {chargement ? (
          <div className="p-6">
            <SqueletteTableau lignes={5} />
          </div>
        ) : (
          <div className="overflow-x-auto">
            {seancesFiltrees.length === 0 ? (
              <div className="text-center py-12 text-xs text-slate-400 dark:text-slate-500">
                Aucune séance trouvée pour cette recherche.
              </div>
            ) : (
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-100 dark:border-sombre-bordure/60 text-slate-400 dark:text-slate-500 font-semibold tracking-wider uppercase text-[11px] bg-slate-50/50 dark:bg-sombre-surface/50">
                    <th className="py-3.5 px-4 sm:px-6">Date</th>
                    <th className="py-3.5 px-4">Cycle</th>
                    <th className="py-3.5 px-4">Statut</th>
                    <th className="py-3.5 px-4 text-right">Caisse Encaissée</th>
                    <th className="py-3.5 px-4">Versement Banque</th>
                    <th className="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure/40">
                  {seancesFiltrees.map((seance) => {
                    const infoVersement = badgesVersement[seance.etat_versement] || {
                      variante: 'neutre' as const,
                      label: seance.etat_versement,
                    };

                    return (
                      <tr
                        key={seance.id}
                        className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/40 transition-colors"
                      >
                        <td className="py-4 px-4 sm:px-6 font-bold text-slate-900 dark:text-white whitespace-nowrap">
                          {formaterDate(seance.date_seance)}
                        </td>
                        <td className="py-4 px-4 text-slate-600 dark:text-slate-300">
                          {seance.cycle?.nom || '-'}
                        </td>
                        <td className="py-4 px-4 whitespace-nowrap">
                          <Badge variante={seance.statut === 'ouverte' ? 'succes' : 'neutre'}>
                            {seance.statut === 'ouverte' ? 'Ouverte' : 'Clôturée'}
                          </Badge>
                        </td>
                        {/* Montant neutre, net et sobre */}
                        <td className="py-4 px-4 text-right font-bold text-slate-900 dark:text-white tabular-nums text-sm whitespace-nowrap">
                          {formaterMontant(seance.total_encaisse)}
                        </td>
                        <td className="py-4 px-4 whitespace-nowrap">
                          <Badge variante={infoVersement.variante}>{infoVersement.label}</Badge>
                        </td>
                        <td className="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                          <Link to={`${prefixRoute}/${seance.id}`}>
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
        )}
      </Carte>
    </div>
  );
};
