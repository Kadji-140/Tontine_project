import React, { useEffect, useState } from 'react';
import { Carte, EnteteCarte, TitreCarte, SousTitreCarte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Alerte } from '../../components/ui/Alerte';
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

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            Sanctions & Amendes Disciplinaires
          </h2>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Suivi des retards, absences non justifiées et amendes de séance
          </p>
        </div>

        {estBureau && (
          <Bouton
            variante="danger"
            onClick={() => setModalOuvert(true)}
            icone={<Plus className="w-4 h-4 mr-1" />}
          >
            Infliger une Sanction
          </Bouton>
        )}
      </div>

      {message && (
        <Alerte variante={message.type === 'succes' ? 'succes' : 'danger'}>
          {message.texte}
        </Alerte>
      )}

      {/* Modal nouvelle sanction */}
      {modalOuvert && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in">
          <div className="w-full max-w-md bg-white dark:bg-sombre-surface rounded-3xl p-6 shadow-2xl border border-slate-100 dark:border-sombre-bordure space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-sombre-bordure">
              <h3 className="text-base font-bold text-slate-900 dark:text-white">Nouvelle Sanction</h3>
              <button onClick={() => setModalOuvert(false)} className="p-1 text-slate-400 hover:text-white">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={creerSanction} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                  Membre sanctionné
                </label>
                <select
                  value={userId}
                  onChange={(e) => setUserId(e.target.value)}
                  className="block w-full rounded-xl border border-slate-300 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2.5 px-3 text-sm text-slate-900 dark:text-white"
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
      <Carte>
        <div className="overflow-x-auto">
          {sanctions.length === 0 ? (
            <div className="text-center py-10 text-xs text-slate-400">
              Aucune sanction enregistrée. Bravo pour la discipline !
            </div>
          ) : (
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-slate-100 dark:border-sombre-bordure text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                  <th className="pb-3 px-3">Membre</th>
                  <th className="pb-3 px-3">Motif</th>
                  <th className="pb-3 px-3 text-right">Montant</th>
                  <th className="pb-3 px-3">Statut</th>
                  <th className="pb-3 px-3 text-right">Date</th>
                  {estBureau && <th className="pb-3 px-3 text-right">Actions</th>}
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure">
                {sanctions.map((s) => (
                  <tr key={s.id} className="hover:bg-slate-50/50 dark:hover:bg-sombre-carte/50 transition-colors">
                    <td className="py-3.5 px-3 font-semibold text-slate-900 dark:text-white">
                      {s.user?.name}
                    </td>
                    <td className="py-3.5 px-3 text-slate-600 dark:text-slate-300">
                      {s.motif}
                    </td>
                    <td className="py-3.5 px-3 text-right font-bold text-slate-900 dark:text-white">
                      {formaterMontant(s.montant)}
                    </td>
                    <td className="py-3.5 px-3">
                      <Badge variante={s.est_reglee ? 'succes' : 'danger'}>
                        {s.est_reglee ? 'Réglée' : 'Non payée'}
                      </Badge>
                    </td>
                    <td className="py-3.5 px-3 text-right text-slate-400">
                      {formaterDate(s.created_at)}
                    </td>
                    {estBureau && (
                      <td className="py-3.5 px-3 text-right space-x-2 whitespace-nowrap">
                        {!s.est_reglee && (
                          <Bouton
                            variante="primaire"
                            taille="sm"
                            onClick={() => payerSanction(s.id)}
                            icone={<CheckCircle2 className="w-3.5 h-3.5 mr-1" />}
                          >
                            Encaisser
                          </Bouton>
                        )}
                        {!s.est_reglee && (
                          <Bouton
                            variante="contour"
                            taille="sm"
                            onClick={() => supprimerSanction(s.id)}
                            icone={<Trash2 className="w-3.5 h-3.5" />}
                          />
                        )}
                      </td>
                    )}
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
