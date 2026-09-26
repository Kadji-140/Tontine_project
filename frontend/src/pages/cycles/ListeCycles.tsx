import React, { useEffect, useState } from 'react';
import { Carte, EnteteCarte, TitreCarte, SousTitreCarte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Alerte } from '../../components/ui/Alerte';
import api from '../../lib/api';
import { Cycle } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { useAuthStore } from '../../stores/authStore';
import { Repeat, Plus, CheckCircle, Users, Shuffle, X } from 'lucide-react';

export const ListeCycles: React.FC = () => {
  const { estBureau } = useAuthStore();
  const [cycles, setCycles] = useState<Cycle[]>([]);
  const [chargement, setChargement] = useState(true);
  const [modalOuvert, setModalOuvert] = useState(false);
  const [modalMembres, setModalMembres] = useState<Cycle | null>(null);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  // Formulaire nouveau cycle
  const [form, setForm] = useState({
    nom: '',
    date_debut: '',
    date_fin: '',
    montant_part: '10000',
    montant_mange_mille: '1000',
    taux_interet: '10',
    frequence_paiement: 'mensuelle',
    est_actif: true,
  });

  const chargerCycles = async () => {
    setChargement(true);
    try {
      const rep = await api.get<{ succes: boolean; cycles: Cycle[] }>('/cycles');
      if (rep.data.succes) setCycles(rep.data.cycles);
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerCycles();
  }, []);

  const creerCycle = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const rep = await api.post('/cycles', {
        ...form,
        montant_part: Number(form.montant_part),
        montant_mange_mille: Number(form.montant_mange_mille),
        taux_interet: Number(form.taux_interet),
      });
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: 'Cycle créé avec succès.' });
        setModalOuvert(false);
        chargerCycles();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors de la création du cycle.' });
    }
  };

  const activerCycle = async (id: number) => {
    try {
      const rep = await api.post(`/cycles/${id}/activer`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerCycles();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || "Impossible d'activer le cycle." });
    }
  };

  const melangerOrdre = async (id: number) => {
    if (!confirm('Voulez-vous procéder au tirage au sort aléatoire des rangs de passage ?')) return;
    try {
      const rep = await api.post(`/cycles/${id}/membres/aleatoire`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerCycles();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Tirage au sort impossible.' });
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            Cycles de Tontine
          </h2>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Paramétrez les périodes, les cotisations, les taux d'intérêt et l'ordre de passage
          </p>
        </div>

        {estBureau && (
          <Bouton
            variante="primaire"
            onClick={() => setModalOuvert(true)}
            icone={<Plus className="w-4 h-4 mr-1" />}
          >
            Nouveau Cycle
          </Bouton>
        )}
      </div>

      {message && (
        <Alerte variante={message.type === 'succes' ? 'succes' : 'danger'}>
          {message.texte}
        </Alerte>
      )}

      {/* Modal de création de cycle */}
      {modalOuvert && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in">
          <div className="w-full max-w-lg bg-white dark:bg-sombre-surface rounded-3xl p-6 shadow-2xl border border-slate-100 dark:border-sombre-bordure space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-sombre-bordure">
              <h3 className="text-base font-bold text-slate-900 dark:text-white">Créer un Nouveau Cycle</h3>
              <button onClick={() => setModalOuvert(false)} className="p-1 text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={creerCycle} className="space-y-3.5">
              <ChampTexte
                etiquette="Nom du cycle"
                required
                placeholder="Ex: Cycle Annuel 2026"
                value={form.nom}
                onChange={(e) => setForm({ ...form, nom: e.target.value })}
              />

              <div className="grid grid-cols-2 gap-3">
                <ChampTexte
                  etiquette="Date de début"
                  type="date"
                  required
                  value={form.date_debut}
                  onChange={(e) => setForm({ ...form, date_debut: e.target.value })}
                />
                <ChampTexte
                  etiquette="Date de fin"
                  type="date"
                  required
                  value={form.date_fin}
                  onChange={(e) => setForm({ ...form, date_fin: e.target.value })}
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <ChampTexte
                  etiquette="Montant de la part (F)"
                  type="number"
                  required
                  value={form.montant_part}
                  onChange={(e) => setForm({ ...form, montant_part: e.target.value })}
                />
                <ChampTexte
                  etiquette="Mange-Mille / Collation (F)"
                  type="number"
                  value={form.montant_mange_mille}
                  onChange={(e) => setForm({ ...form, montant_mange_mille: e.target.value })}
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <ChampTexte
                  etiquette="Taux d'intérêt prêt (%)"
                  type="number"
                  step="0.5"
                  required
                  value={form.taux_interet}
                  onChange={(e) => setForm({ ...form, taux_interet: e.target.value })}
                />

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                    Fréquence
                  </label>
                  <select
                    value={form.frequence_paiement}
                    onChange={(e) => setForm({ ...form, frequence_paiement: e.target.value })}
                    className="block w-full rounded-xl border border-slate-300 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2.5 px-3 text-sm text-slate-900 dark:text-white"
                  >
                    <option value="mensuelle">Mensuelle</option>
                    <option value="hebdomadaire">Hebdomadaire</option>
                    <option value="bimensuelle">Bimensuelle</option>
                  </select>
                </div>
              </div>

              <div className="flex gap-3 pt-2">
                <Bouton type="button" variante="contour" onClick={() => setModalOuvert(false)} className="flex-1 justify-center">
                  Annuler
                </Bouton>
                <Bouton type="submit" variante="primaire" className="flex-1 justify-center">
                  Créer le cycle
                </Bouton>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Grille des cycles */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {cycles.map((cycle) => (
          <Carte key={cycle.id} surbrillance={cycle.est_actif} className="space-y-4">
            <div className="flex items-start justify-between">
              <div>
                <div className="flex items-center gap-2">
                  <h3 className="font-bold text-base text-slate-900 dark:text-white">{cycle.nom}</h3>
                </div>
                <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  Du {formaterDate(cycle.date_debut)} au {formaterDate(cycle.date_fin)}
                </p>
              </div>
              <Badge variante={cycle.est_actif ? 'succes' : 'neutre'}>
                {cycle.est_actif ? 'Actif' : 'Clôturé / Inactif'}
              </Badge>
            </div>

            <div className="grid grid-cols-2 gap-3 py-3 border-y border-slate-100 dark:border-sombre-bordure text-xs">
              <div>
                <span className="text-slate-400 block text-[11px]">Montant Part</span>
                <span className="font-bold text-slate-900 dark:text-white">{formaterMontant(cycle.montant_part)}</span>
              </div>
              <div>
                <span className="text-slate-400 block text-[11px]">Mange-Mille</span>
                <span className="font-bold text-slate-900 dark:text-white">{formaterMontant(cycle.montant_mange_mille)}</span>
              </div>
              <div>
                <span className="text-slate-400 block text-[11px]">Taux Prêt</span>
                <span className="font-bold text-slate-900 dark:text-white">{cycle.taux_interet}%</span>
              </div>
              <div>
                <span className="text-slate-400 block text-[11px]">Fréquence</span>
                <span className="font-semibold text-slate-700 dark:text-slate-300 capitalize">{cycle.frequence_paiement}</span>
              </div>
            </div>

            <div className="flex items-center justify-between pt-1 gap-2">
              {estBureau && !cycle.est_actif && (
                <Bouton
                  variante="contour"
                  taille="sm"
                  onClick={() => activerCycle(cycle.id)}
                  icone={<CheckCircle className="w-3.5 h-3.5 mr-1" />}
                >
                  Activer
                </Bouton>
              )}

              {estBureau && (
                <Bouton
                  variante="contour"
                  taille="sm"
                  onClick={() => melangerOrdre(cycle.id)}
                  icone={<Shuffle className="w-3.5 h-3.5 mr-1" />}
                >
                  Tirage Sort
                </Bouton>
              )}
            </div>
          </Carte>
        ))}
      </div>
    </div>
  );
};
