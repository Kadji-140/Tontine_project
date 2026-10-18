import React, { useEffect, useState, useMemo } from 'react';
import { Carte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Alerte } from '../../components/ui/Alerte';
import { BarreFiltresTableau, OptionFiltre } from '../../components/ui/BarreFiltresTableau';
import { SqueletteTableau } from '../../components/ui/SqueletteChargement';
import api from '../../lib/api';
import { Pret, Seance } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { useAuthStore } from '../../stores/authStore';
import { HandCoins, Plus, Check, X } from 'lucide-react';

export const ListePrets: React.FC = () => {
  const { utilisateur, estBureau } = useAuthStore();
  const [prets, setPrets] = useState<Pret[]>([]);
  const [seancesOuvertes, setSeancesOuvertes] = useState<Seance[]>([]);
  const [chargement, setChargement] = useState(true);
  const [modalOuvert, setModalOuvert] = useState(false);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  // Filtres
  const [recherche, setRecherche] = useState('');
  const [filtreStatut, setFiltreStatut] = useState<string>('tous');

  // Formulaire de demande
  const [montant, setMontant] = useState('');
  const [dateEcheance, setDateEcheance] = useState('');
  const [seanceId, setSeanceId] = useState('');
  const [soumissionEnCours, setSoumissionEnCours] = useState(false);

  const chargerPrets = async () => {
    setChargement(true);
    try {
      const [repPrets, repSeances] = await Promise.all([
        api.get<{ succes: boolean; prets: Pret[] }>('/prets'),
        api.get<{ succes: boolean; seances: Seance[] }>('/seances'),
      ]);

      if (repPrets.data.succes) setPrets(repPrets.data.prets);
      if (repSeances.data.succes) {
        const ouvertes = repSeances.data.seances.filter((s) => s.statut === 'ouverte');
        setSeancesOuvertes(ouvertes);
        if (ouvertes.length > 0) setSeanceId(String(ouvertes[0].id));
      }
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerPrets();
  }, []);

  const creerDemande = async (e: React.FormEvent) => {
    e.preventDefault();
    setSoumissionEnCours(true);
    setMessage(null);

    try {
      const rep = await api.post('/prets', {
        seance_id: seanceId,
        montant_demande: Number(montant),
        date_echeance: dateEcheance,
      });

      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        setModalOuvert(false);
        setMontant('');
        setDateEcheance('');
        chargerPrets();
      }
    } catch (err: any) {
      setMessage({
        type: 'danger',
        texte: err.response?.data?.message || 'Erreur lors de la demande de prêt.',
      });
    } finally {
      setSoumissionEnCours(false);
    }
  };

  const accepterEcheance = async (id: number) => {
    try {
      const rep = await api.put(`/prets/${id}/accepter`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerPrets();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || "Impossible d'accepter." });
    }
  };

  const validerPret = async (id: number) => {
    if (!confirm('Confirmez-vous le décaissement physique de ce prêt ?')) return;
    try {
      const rep = await api.post(`/prets/${id}/valider`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerPrets();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors de la validation.' });
    }
  };

  // Filtrage combiné
  const pretsFiltres = useMemo(() => {
    return prets.filter((p) => {
      const matchRecherche =
        !recherche ||
        (p.user?.name && p.user.name.toLowerCase().includes(recherche.toLowerCase())) ||
        String(p.montant_demande).includes(recherche);

      const matchStatut =
        filtreStatut === 'tous' || p.statut === filtreStatut;

      return matchRecherche && matchStatut;
    });
  }, [prets, recherche, filtreStatut]);

  const optionsFiltres: OptionFiltre[] = [
    { cle: 'tous', label: 'Tous les crédits', compte: prets.length },
    { cle: 'valide', label: 'En cours (Accordés)', compte: prets.filter((p) => p.statut === 'valide').length },
    { cle: 'en_attente', label: 'En attente', compte: prets.filter((p) => p.statut === 'en_attente').length },
    { cle: 'rembourse', label: 'Soldés', compte: prets.filter((p) => p.statut === 'rembourse').length },
  ];

  const badgesStatut = {
    en_attente: { variante: 'alerte' as const, label: 'En attente' },
    valide: { variante: 'succes' as const, label: 'Accordé & Décaissé' },
    rembourse: { variante: 'info' as const, label: 'Soldé' },
    rejete: { variante: 'danger' as const, label: 'Refusé' },
  };

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
            Gestion des Prêts & Crédits
          </h2>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Sollicitez un prêt d'entraide ou validez les décaissements de séance
          </p>
        </div>

        <Bouton
          variante="primaire"
          onClick={() => setModalOuvert(true)}
          icone={<Plus className="w-4 h-4 mr-1" />}
        >
          Nouvelle Demande
        </Bouton>
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
        placeholder="Rechercher par membre emprunteur, montant..."
        optionsFiltres={optionsFiltres}
        filtreActif={filtreStatut}
        surChangementFiltre={setFiltreStatut}
        totalResultats={pretsFiltres.length}
        libelleResultats="prêt(s) listé(s)"
      />

      {/* Modal de demande de prêt */}
      {modalOuvert && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in">
          <div className="w-full max-w-md bg-white dark:bg-sombre-surface rounded-2xl p-6 shadow-2xl border border-slate-200/80 dark:border-sombre-bordure space-y-5">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-sombre-bordure">
              <h3 className="text-base font-bold text-slate-900 dark:text-white">
                Demande de Prêt Solidaire
              </h3>
              <button
                onClick={() => setModalOuvert(false)}
                className="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={creerDemande} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                  Séance de rattachement
                </label>
                <select
                  value={seanceId}
                  onChange={(e) => setSeanceId(e.target.value)}
                  className="block w-full rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2 px-3 text-xs text-slate-900 dark:text-white"
                  required
                >
                  {seancesOuvertes.length === 0 ? (
                    <option value="">Aucune séance ouverte actuellement</option>
                  ) : (
                    seancesOuvertes.map((s) => (
                      <option key={s.id} value={s.id}>
                        Séance du {formaterDate(s.date_seance)} ({s.cycle?.nom})
                      </option>
                    ))
                  )}
                </select>
              </div>

              <ChampTexte
                etiquette="Montant souhaité (FCFA)"
                type="number"
                min="1000"
                step="500"
                required
                placeholder="Ex: 50 000"
                value={montant}
                onChange={(e) => setMontant(e.target.value)}
              />

              <ChampTexte
                etiquette="Date d'échéance souhaitée"
                type="date"
                required
                value={dateEcheance}
                onChange={(e) => setDateEcheance(e.target.value)}
                aide="Maximum 3 mois ou fin du cycle actif."
              />

              <div className="flex gap-3 pt-2">
                <Bouton
                  type="button"
                  variante="contour"
                  onClick={() => setModalOuvert(false)}
                  className="flex-1 justify-center"
                >
                  Annuler
                </Bouton>
                <Bouton
                  type="submit"
                  variante="primaire"
                  disabled={soumissionEnCours}
                  className="flex-1 justify-center"
                >
                  {soumissionEnCours ? 'Envoi...' : 'Soumettre'}
                </Bouton>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Liste des prêts */}
      <Carte className="p-0 overflow-hidden border border-slate-200/80 dark:border-sombre-bordure">
        {chargement ? (
          <div className="p-6">
            <SqueletteTableau lignes={5} />
          </div>
        ) : (
          <div className="overflow-x-auto">
            {pretsFiltres.length === 0 ? (
              <div className="text-center py-12 text-xs text-slate-400 dark:text-slate-500">
                Aucun prêt ne correspond à vos critères.
              </div>
            ) : (
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-100 dark:border-sombre-bordure/60 text-slate-400 dark:text-slate-500 font-semibold tracking-wider uppercase text-[11px] bg-slate-50/50 dark:bg-sombre-surface/50">
                    <th className="py-3.5 px-4 sm:px-6">Bénéficiaire</th>
                    <th className="py-3.5 px-4 text-right">Capital</th>
                    <th className="py-3.5 px-4 text-right">Intérêts</th>
                    <th className="py-3.5 px-4 text-right">Total Dû</th>
                    <th className="py-3.5 px-4">Échéance</th>
                    <th className="py-3.5 px-4">Statut</th>
                    <th className="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure/40">
                  {pretsFiltres.map((pret) => {
                    const infoStatut = badgesStatut[pret.statut] || {
                      variante: 'neutre' as const,
                      label: pret.statut,
                    };

                    const attendConfirmationMembre =
                      pret.date_echeance_modifiee && !pret.est_accepte_par_membre && pret.statut === 'en_attente';

                    const estMonPret = pret.user_id === utilisateur?.id;

                    return (
                      <tr
                        key={pret.id}
                        className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/40 transition-colors"
                      >
                        <td className="py-4 px-4 sm:px-6 font-bold text-slate-900 dark:text-white">
                          {pret.user?.name || 'Membre'}
                        </td>
                        <td className="py-4 px-4 text-right font-semibold text-slate-900 dark:text-white tabular-nums">
                          {formaterMontant(pret.montant_demande)}
                        </td>
                        <td className="py-4 px-4 text-right text-slate-500 dark:text-slate-400 tabular-nums">
                          +{formaterMontant(pret.interet_total)}
                        </td>
                        <td className="py-4 px-4 text-right font-bold text-slate-900 dark:text-white tabular-nums text-sm">
                          {formaterMontant(pret.total_a_rembourser || (pret.montant_demande + pret.interet_total))}
                        </td>
                        <td className="py-4 px-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                          {formaterDate(pret.date_echeance)}
                          {attendConfirmationMembre && (
                            <span className="block text-[10px] text-amber-500 font-bold">
                              Date réajustée
                            </span>
                          )}
                        </td>
                        <td className="py-4 px-4 whitespace-nowrap">
                          <Badge variante={infoStatut.variante}>{infoStatut.label}</Badge>
                        </td>
                        <td className="py-4 px-4 sm:px-6 text-right space-x-2 whitespace-nowrap">
                          {attendConfirmationMembre && estMonPret && (
                            <Bouton
                              variante="primaire"
                              taille="sm"
                              onClick={() => accepterEcheance(pret.id)}
                              icone={<Check className="w-3.5 h-3.5 mr-1" />}
                            >
                              Accepter Date
                            </Bouton>
                          )}

                          {estBureau && pret.statut === 'en_attente' && pret.est_accepte_par_membre && (
                            <Bouton
                              variante="primaire"
                              taille="sm"
                              onClick={() => validerPret(pret.id)}
                              icone={<Check className="w-3.5 h-3.5 mr-1" />}
                            >
                              Décaisser
                            </Bouton>
                          )}
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
