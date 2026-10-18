import React, { useEffect, useState, useMemo } from 'react';
import { Carte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Alerte } from '../../components/ui/Alerte';
import { BarreFiltresTableau, OptionFiltre } from '../../components/ui/BarreFiltresTableau';
import { SqueletteTableau } from '../../components/ui/SqueletteChargement';
import api from '../../lib/api';
import { Sanction, Utilisateur } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { useAuthStore } from '../../stores/authStore';
import { Scale, Plus, CheckCircle2, Trash2, X } from 'lucide-react';

export const ListeSanctions: React.FC = () => {
  const { estBureau } = useAuthStore();
  const [sanctions, setSanctions] = useState<Sanction[]>([]);
  const [membres, setMembres] = useState<Utilisateur[]>([]);
  const [chargement, setChargement] = useState(true);
  const [modalOuvert, setModalOuvert] = useState(false);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  // Filtres
  const [recherche, setRecherche] = useState('');
  const [filtreStatut, setFiltreStatut] = useState<string>('tous');

  const [userId, setUserId] = useState('');
  const [montant, setMontant] = useState('1000');
  const [motif, setMotif] = useState('');

  const chargerDonnees = async () => {
    setChargement(true);
    try {
      const [repSanctions, repUsers] = await Promise.all([
        api.get<{ succes: boolean; sanctions: Sanction[] }>('/sanctions'),
        estBureau ? api.get<{ succes: boolean; utilisateurs: Utilisateur[] }>('/utilisateurs') : Promise.resolve({ data: { utilisateurs: [] } }),
      ]);

      if (repSanctions.data.succes) setSanctions(repSanctions.data.sanctions);
      if (repUsers.data.utilisateurs) {
        setMembres(repUsers.data.utilisateurs);
        if (repUsers.data.utilisateurs.length > 0) setUserId(String(repUsers.data.utilisateurs[0].id));
      }
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerDonnees();
  }, [estBureau]);

  const creerSanction = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const rep = await api.post('/sanctions', {
        user_id: Number(userId),
        montant: Number(montant),
        motif,
      });

      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: 'Sanction enregistrée avec succès.' });
        setModalOuvert(false);
        setMotif('');
        chargerDonnees();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || "Erreur lors de l'enregistrement." });
    }
  };

  const payerSanction = async (id: number) => {
    if (!confirm('Confirmez-vous le règlement de cette amende ? Le montant entrera dans la séance ouverte.')) return;
    try {
      const rep = await api.put(`/sanctions/${id}/payer`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerDonnees();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors du paiement.' });
    }
  };

  const supprimerSanction = async (id: number) => {
    if (!confirm('Voulez-vous annuler cette sanction ?')) return;
    try {
      const rep = await api.delete(`/sanctions/${id}`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerDonnees();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || "Impossible de supprimer." });
    }
  };

  // Filtrage combiné
  const sanctionsFiltrees = useMemo(() => {
    return sanctions.filter((s) => {
      const matchRecherche =
        !recherche ||
        (s.user?.name && s.user.name.toLowerCase().includes(recherche.toLowerCase())) ||
        (s.motif && s.motif.toLowerCase().includes(recherche.toLowerCase()));

      const matchStatut =
        filtreStatut === 'tous' ||
        (filtreStatut === 'reglee' && s.est_reglee) ||
        (filtreStatut === 'impayee' && !s.est_reglee);

      return matchRecherche && matchStatut;
    });
  }, [sanctions, recherche, filtreStatut]);

  const optionsFiltres: OptionFiltre[] = [
    { cle: 'tous', label: 'Toutes les sanctions', compte: sanctions.length },
    { cle: 'impayee', label: 'Impayées', compte: sanctions.filter((s) => !s.est_reglee).length },
    { cle: 'reglee', label: 'Réglées', compte: sanctions.filter((s) => s.est_reglee).length },
  ];

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
            Sanctions & Amendes Disciplinaires
          </h2>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Suivi des pénalités statutaires pour retards, absences ou manquements
          </p>
        </div>

        {estBureau && (
          <Bouton
            variante="primaire"
            onClick={() => setModalOuvert(true)}
            icone={<Plus className="w-4 h-4 mr-1" />}
          >
            Nouvelle Sanction
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
        placeholder="Rechercher par membre, motif..."
        optionsFiltres={optionsFiltres}
        filtreActif={filtreStatut}
        surChangementFiltre={setFiltreStatut}
        totalResultats={sanctionsFiltrees.length}
        libelleResultats="sanction(s) listée(s)"
      />

      {/* Modal nouvelle sanction */}
      {modalOuvert && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in">
          <div className="w-full max-w-md bg-white dark:bg-sombre-surface rounded-2xl p-6 shadow-2xl border border-slate-200/80 dark:border-sombre-bordure space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-sombre-bordure">
              <h3 className="text-base font-bold text-slate-900 dark:text-white">Nouvelle Sanction</h3>
              <button onClick={() => setModalOuvert(false)} className="p-1 text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={creerSanction} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                  Membre sanctionné
                </label>
                <select
                  value={userId}
                  onChange={(e) => setUserId(e.target.value)}
                  className="block w-full rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2 px-3 text-xs text-slate-900 dark:text-white"
                  required
                >
                  {membres.map((m) => (
                    <option key={m.id} value={m.id}>
                      {m.name} ({m.phone})
                    </option>
                  ))}
                </select>
              </div>

              <ChampTexte
                etiquette="Montant de l'amende (FCFA)"
                type="number"
                min="100"
                step="500"
                required
                value={montant}
                onChange={(e) => setMontant(e.target.value)}
              />

              <ChampTexte
                etiquette="Motif"
                required
                placeholder="Ex: Retard de 45 minutes à la séance"
                value={motif}
                onChange={(e) => setMotif(e.target.value)}
              />

              <div className="flex gap-3 pt-2">
                <Bouton type="button" variante="contour" onClick={() => setModalOuvert(false)} className="flex-1 justify-center">
                  Annuler
                </Bouton>
                <Bouton type="submit" variante="danger" className="flex-1 justify-center">
                  Enregistrer
                </Bouton>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Table des sanctions */}
      <Carte className="p-0 overflow-hidden border border-slate-200/80 dark:border-sombre-bordure">
        {chargement ? (
          <div className="p-6">
            <SqueletteTableau lignes={4} />
          </div>
        ) : (
          <div className="overflow-x-auto">
            {sanctionsFiltrees.length === 0 ? (
              <div className="text-center py-12 text-xs text-slate-400 dark:text-slate-500">
                Aucune sanction répertoriée pour cette recherche.
              </div>
            ) : (
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-100 dark:border-sombre-bordure/60 text-slate-400 dark:text-slate-500 font-semibold tracking-wider uppercase text-[11px] bg-slate-50/50 dark:bg-sombre-surface/50">
                    <th className="py-3.5 px-4 sm:px-6">Membre</th>
                    <th className="py-3.5 px-4">Motif</th>
                    <th className="py-3.5 px-4 text-right">Montant</th>
                    <th className="py-3.5 px-4">Statut</th>
                    <th className="py-3.5 px-4 text-right">Date</th>
                    {estBureau && <th className="py-3.5 px-4 sm:px-6 text-right">Actions</th>}
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure/40">
                  {sanctionsFiltrees.map((s) => (
                    <tr
                      key={s.id}
                      className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/40 transition-colors"
                    >
                      <td className="py-4 px-4 sm:px-6 font-bold text-slate-900 dark:text-white">
                        {s.user?.name || 'Membre'}
                      </td>
                      <td className="py-4 px-4 text-slate-600 dark:text-slate-300">
                        {s.motif}
                      </td>
                      <td className="py-4 px-4 text-right font-bold text-slate-900 dark:text-white tabular-nums text-sm">
                        {formaterMontant(s.montant)}
                      </td>
                      <td className="py-4 px-4 whitespace-nowrap">
                        <Badge variante={s.est_reglee ? 'succes' : 'danger'}>
                          {s.est_reglee ? 'Réglée' : 'Non payée'}
                        </Badge>
                      </td>
                      <td className="py-4 px-4 text-right text-slate-400 dark:text-slate-500 whitespace-nowrap">
                        {formaterDate(s.created_at)}
                      </td>
                      {estBureau && (
                        <td className="py-4 px-4 sm:px-6 text-right whitespace-nowrap space-x-2">
                          {!s.est_reglee && (
                            <Bouton
                              variante="primaire"
                              taille="sm"
                              onClick={() => payerSanction(s.id)}
                              icone={<CheckCircle2 className="w-3.5 h-3.5 mr-1" />}
                            >
                              Régler
                            </Bouton>
                          )}
                          <button
                            onClick={() => supprimerSanction(s.id)}
                            className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors inline-block"
                            title="Annuler la sanction"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </td>
                      )}
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
