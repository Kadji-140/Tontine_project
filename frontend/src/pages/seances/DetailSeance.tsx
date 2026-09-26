import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Carte, EnteteCarte, TitreCarte, SousTitreCarte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Alerte } from '../../components/ui/Alerte';
import api from '../../lib/api';
import { Seance, Utilisateur, Cotisation, PaiementLot } from '../../types';
import { formaterDate, formaterMontant } from '../../lib/utils';
import { useAuthStore } from '../../stores/authStore';
import {
  Calendar,
  Wallet,
  ArrowLeft,
  FileDown,
  UserCheck,
  Plus,
  Trash2,
  Upload,
  CheckCircle,
  XCircle,
  Coins,
  Gift
} from 'lucide-react';

export const DetailSeance: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const { estBureau, utilisateur } = useAuthStore();

  const [seance, setSeance] = useState<Seance | null>(null);
  const [beneficiaireAttendu, setBeneficiaireAttendu] = useState<Utilisateur | null>(null);
  const [membresActifs, setMembresActifs] = useState<Utilisateur[]>([]);
  const [cotisations, setCotisations] = useState<Cotisation[]>([]);
  const [chargement, setChargement] = useState(true);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  // Formulaire Cotisation
  const [membreId, setMembreId] = useState('');
  const [montantCotisation, setMontantCotisation] = useState('');
  const [typeCotisation, setTypeCotisation] = useState<'tontine' | 'secours'>('tontine');
  const [enregistrementCotisation, setEnregistrementCotisation] = useState(false);

  // Formulaire Versement Lot (Pot)
  const [beneficiaireLotId, setBeneficiaireLotId] = useState('');
  const [montantLot, setMontantLot] = useState('');
  const [versementLotEnCours, setVersementLotEnCours] = useState(false);

  // Upload preuve
  const [fichierPreuve, setFichierPreuve] = useState<File | null>(null);
  const [uploadEnCours, setUploadEnCours] = useState(false);

  const chargerDetails = async () => {
    setChargement(true);
    try {
      const rep = await api.get<{
        succes: boolean;
        seance: Seance;
        beneficiaire_attendu: Utilisateur | null;
        membres_actifs: Utilisateur[];
      }>(`/seances/${id}`);

      if (rep.data.succes) {
        setSeance(rep.data.seance);
        setBeneficiaireAttendu(rep.data.beneficiaire_attendu);
        setMembresActifs(rep.data.membres_actifs);
        setCotisations(rep.data.seance.cotisations || []);

        if (rep.data.membres_actifs.length > 0) {
          setMembreId(String(rep.data.membres_actifs[0].id));
        }

        if (rep.data.beneficiaire_attendu) {
          setBeneficiaireLotId(String(rep.data.beneficiaire_attendu.id));
        }
      }
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerDetails();
  }, [id]);

  const soumettreCotisation = async (e: React.FormEvent) => {
    e.preventDefault();
    setEnregistrementCotisation(true);
    setMessage(null);

    try {
      const rep = await api.post('/cotisations', {
        seance_id: Number(id),
        user_id: Number(membreId),
        montant: Number(montantCotisation),
        type: typeCotisation,
      });

      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        setMontantCotisation('');
        chargerDetails();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors de l’enregistrement.' });
    } finally {
      setEnregistrementCotisation(false);
    }
  };

  const annulerCotisation = async (cotisationId: number) => {
    if (!confirm('Voulez-vous vraiment annuler cette cotisation ? Le montant sera retiré de la caisse.')) return;
    try {
      const rep = await api.delete(`/cotisations/${cotisationId}`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerDetails();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || "Impossible d'annuler." });
    }
  };

  const soumettrePaiementLot = async (e: React.FormEvent) => {
    e.preventDefault();
    setVersementLotEnCours(true);
    setMessage(null);

    try {
      const rep = await api.post('/paiements-lots', {
        seance_id: Number(id),
        user_id: Number(beneficiaireLotId),
        montant: Number(montantLot),
      });

      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        setMontantLot('');
        chargerDetails();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors du versement du lot.' });
    } finally {
      setVersementLotEnCours(false);
    }
  };

  const envoyerPreuve = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!fichierPreuve) return;

    setUploadEnCours(true);
    const formData = new FormData();
    formData.append('preuve', fichierPreuve);

    try {
      const rep = await api.post(`/seances/${id}/preuve`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        setFichierPreuve(null);
        chargerDetails();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || "Erreur lors de l'envoi." });
    } finally {
      setUploadEnCours(false);
    }
  };

  if (chargement || !seance) {
    return <div className="p-8 text-center text-xs text-slate-400">Chargement de la séance...</div>;
  }

  return (
    <div className="space-y-6">
      {/* Navigation et En-tête */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <Link to="/seances">
            <Bouton variante="contour" taille="sm" icone={<ArrowLeft className="w-4 h-4" />}>
              Retour
            </Bouton>
          </Link>
          <div>
            <h2 className="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
              Séance du {formaterDate(seance.date_seance)}
            </h2>
            <p className="text-xs text-slate-500 dark:text-slate-400">
              {seance.cycle?.nom} • Statut : <span className="capitalize font-semibold">{seance.statut}</span>
            </p>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <a
            href={`http://127.0.0.1:8000/api/seances/${id}/rapport-pdf`}
            target="_blank"
            rel="noreferrer"
          >
            <Bouton variante="contour" taille="sm" icone={<FileDown className="w-4 h-4 mr-1" />}>
              Télécharger Rapport PDF
            </Bouton>
          </a>
        </div>
      </div>

      {message && (
        <Alerte variante={message.type === 'succes' ? 'succes' : 'danger'}>
          {message.texte}
        </Alerte>
      )}

      {/* Cartes résumé & Bénéficiaire attendu */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Carte className="bg-gradient-to-tr from-primaire-600 to-emerald-700 text-white border-0 shadow-lg shadow-primaire-600/20">
          <div className="flex items-center justify-between pb-3 border-b border-white/10">
            <span className="text-xs font-semibold text-primaire-100 uppercase tracking-wider">
              Caisse Physique du Jour
            </span>
            <Wallet className="w-5 h-5 text-white/80" />
          </div>
          <div className="pt-3">
            <p className="text-3xl font-black tracking-tight">{formaterMontant(seance.total_encaisse)}</p>
            <p className="text-xs text-primaire-100 mt-1">Montant disponible sur table</p>
          </div>
        </Carte>

        <Carte className="md:col-span-2">
          <div className="flex items-start justify-between">
            <div className="space-y-1">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                Bénéficiaire Théorique du Tour
              </span>
              <h4 className="text-base font-bold text-slate-900 dark:text-white">
                {beneficiaireAttendu?.name || "Non défini ou fin de cycle"}
              </h4>
              <p className="text-xs text-slate-500 dark:text-slate-400">
                {beneficiaireAttendu ? `Téléphone : ${beneficiaireAttendu.phone}` : "Aucun rang assigné pour ce numéro de séance"}
              </p>
            </div>
            <div className="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
              <Gift className="w-6 h-6" />
            </div>
          </div>
        </Carte>
      </div>

      {/* Section Bureau : Saisie des Cotisations & Paiement du Lot */}
      {estBureau && seance.statut === 'ouverte' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Formulaire Cotisation */}
          <Carte>
            <EnteteCarte>
              <div>
                <TitreCarte>Enregistrer une Cotisation</TitreCarte>
                <SousTitreCarte>
                  Déduction automatique du Mange-Mille ({formaterMontant(seance.cycle?.montant_mange_mille)})
                </SousTitreCarte>
              </div>
            </EnteteCarte>

            <form onSubmit={soumettreCotisation} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                  Membre cotisant
                </label>
                <select
                  value={membreId}
                  onChange={(e) => setMembreId(e.target.value)}
                  className="block w-full rounded-xl border border-slate-300 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2.5 px-3 text-sm text-slate-900 dark:text-white"
                  required
                >
                  {membresActifs.map((m) => (
                    <option key={m.id} value={m.id}>
                      {m.name} ({m.phone})
                    </option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <ChampTexte
                  etiquette="Montant versé (FCFA)"
                  type="number"
                  min="1"
                  required
                  placeholder="Ex: 11 000"
                  value={montantCotisation}
                  onChange={(e) => setMontantCotisation(e.target.value)}
                />

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                    Type
                  </label>
                  <select
                    value={typeCotisation}
                    onChange={(e) => setTypeCotisation(e.target.value as any)}
                    className="block w-full rounded-xl border border-slate-300 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2.5 px-3 text-sm text-slate-900 dark:text-white"
                  >
                    <option value="tontine">Tontine</option>
                    <option value="secours">Secours</option>
                  </select>
                </div>
              </div>

              <Bouton
                type="submit"
                variante="primaire"
                chargement={enregistrementCotisation}
                className="w-full justify-center"
                icone={<Plus className="w-4 h-4 mr-1" />}
              >
                Encaisser Cotisation
              </Bouton>
            </form>
          </Carte>

          {/* Formulaire Versement du Lot */}
          <Carte>
            <EnteteCarte>
              <div>
                <TitreCarte>Verser le Gain du Tour (Lot)</TitreCarte>
                <SousTitreCarte>Décaissement de la cagnotte tontine au bénéficiaire</SousTitreCarte>
              </div>
            </EnteteCarte>

            <form onSubmit={soumettrePaiementLot} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                  Bénéficiaire du lot
                </label>
                <select
                  value={beneficiaireLotId}
                  onChange={(e) => setBeneficiaireLotId(e.target.value)}
                  className="block w-full rounded-xl border border-slate-300 dark:border-sombre-bordure bg-white dark:bg-sombre-carte py-2.5 px-3 text-sm text-slate-900 dark:text-white"
                  required
                >
                  {membresActifs.map((m) => (
                    <option key={m.id} value={m.id}>
                      {m.name}
                    </option>
                  ))}
                </select>
              </div>

              <ChampTexte
                etiquette="Montant du lot (FCFA)"
                type="number"
                min="1"
                required
                placeholder="Ex: 100 000"
                value={montantLot}
                onChange={(e) => setMontantLot(e.target.value)}
              />

              <Bouton
                type="submit"
                variante="secondaire"
                chargement={versementLotEnCours}
                className="w-full justify-center"
                icone={<Gift className="w-4 h-4 mr-1" />}
              >
                Valider le Versement du Lot
              </Bouton>
            </form>
          </Carte>
        </div>
      )}

      {/* Table des cotisations enregistrées aujourd'hui */}
      <Carte>
        <EnteteCarte>
          <div>
            <TitreCarte>Cotisations Recueillies Lors de Cette Séance</TitreCarte>
            <SousTitreCarte>{cotisations.length} versement(s) enregistré(s)</SousTitreCarte>
          </div>
        </EnteteCarte>

        <div className="overflow-x-auto">
          {cotisations.length === 0 ? (
            <div className="text-center py-10 text-xs text-slate-400">
              Aucune cotisation n'a encore été enregistrée pour cette séance.
            </div>
          ) : (
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-slate-100 dark:border-sombre-bordure text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                  <th className="pb-3 px-3">Membre</th>
                  <th className="pb-3 px-3">Type</th>
                  <th className="pb-3 px-3 text-right">Montant</th>
                  <th className="pb-3 px-3 text-right">Heure</th>
                  {estBureau && <th className="pb-3 px-3 text-right">Action</th>}
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure">
                {cotisations.map((c) => (
                  <tr key={c.id} className="hover:bg-slate-50/50 dark:hover:bg-sombre-carte/50 transition-colors">
                    <td className="py-3 px-3 font-semibold text-slate-900 dark:text-white">
                      {c.user?.name}
                    </td>
                    <td className="py-3 px-3">
                      <Badge variante={c.type === 'tontine' ? 'primaire' : 'info'} taille="sm">
                        {c.type}
                      </Badge>
                    </td>
                    <td className="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                      {formaterMontant(c.montant)}
                    </td>
                    <td className="py-3 px-3 text-right text-slate-400">
                      {new Date(c.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
                    </td>
                    {estBureau && (
                      <td className="py-3 px-3 text-right">
                        <button
                          onClick={() => annulerCotisation(c.id)}
                          className="p-1 rounded text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                          title="Annuler cette cotisation"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </td>
                    )}
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </Carte>

      {/* Justificatif de Versement Bancaire */}
      <Carte>
        <EnteteCarte>
          <div>
            <TitreCarte>Justificatif de Versement en Banque</TitreCarte>
            <SousTitreCarte>Attestation de dépôt des fonds récoltés sur le compte bancaire</SousTitreCarte>
          </div>
          <Badge
            variante={
              seance.etat_versement === 'valide'
                ? 'succes'
                : seance.etat_versement === 'en_attente'
                ? 'alerte'
                : 'neutre'
            }
          >
            {seance.etat_versement}
          </Badge>
        </EnteteCarte>

        <div className="flex flex-col sm:flex-row items-center gap-6">
          {seance.preuve_versement ? (
            <div className="p-4 rounded-2xl bg-slate-50 dark:bg-sombre-carte border border-slate-200 dark:border-sombre-bordure flex items-center gap-3">
              <CheckCircle className="w-6 h-6 text-emerald-500" />
              <div className="text-xs">
                <p className="font-bold text-slate-900 dark:text-white">Reçu bancaire téléversé</p>
                <a
                  href={`http://127.0.0.1:8000/storage/${seance.preuve_versement}`}
                  target="_blank"
                  rel="noreferrer"
                  className="text-primaire-600 dark:text-primaire-400 hover:underline font-medium"
                >
                  Voir le document
                </a>
              </div>
            </div>
          ) : (
            <p className="text-xs text-slate-500 dark:text-slate-400">
              Aucun justificatif n'a été déposé pour l'instant.
            </p>
          )}

          {estBureau && (
            <form onSubmit={envoyerPreuve} className="flex items-center gap-3 ml-auto">
              <input
                type="file"
                accept="image/*,.pdf"
                onChange={(e) => setFichierPreuve(e.target.files?.[0] || null)}
                className="text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:dark:bg-sombre-carte file:text-slate-700 file:dark:text-slate-300"
              />
              <Bouton
                type="submit"
                variante="contour"
                taille="sm"
                chargement={uploadEnCours}
                disabled={!fichierPreuve}
                icone={<Upload className="w-3.5 h-3.5 mr-1" />}
              >
                Téléverser
              </Bouton>
            </form>
          )}
        </div>
      </Carte>
    </div>
  );
};
