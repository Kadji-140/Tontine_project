import React, { useEffect, useState, useMemo } from 'react';
import { Carte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { Badge } from '../../components/ui/Badge';
import { Alerte } from '../../components/ui/Alerte';
import { Modal } from '../../components/ui/Modal';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { BarreFiltresTableau, OptionFiltre } from '../../components/ui/BarreFiltresTableau';
import { SqueletteTableau } from '../../components/ui/SqueletteChargement';
import api from '../../lib/api';
import { Utilisateur } from '../../types';
import { formaterDate } from '../../lib/utils';
import { useAuthStore } from '../../stores/authStore';
import { 
  UserCheck, 
  UserX, 
  Trash2, 
  Phone, 
  Mail, 
  UserPlus, 
  Link2, 
  Copy, 
  Check, 
  Share2, 
  Lock, 
  User, 
  ShieldCheck, 
  Sparkles,
  Send
} from 'lucide-react';

export const GestionMembres: React.FC = () => {
  const { utilisateur: currentUser } = useAuthStore();
  const [utilisateurs, setUtilisateurs] = useState<Utilisateur[]>([]);
  const [chargement, setChargement] = useState(true);
  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);

  // États pour les filtres et la recherche
  const [recherche, setRecherche] = useState('');
  const [filtreStatut, setFiltreStatut] = useState<string>('tous');
  const [filtreRole, setFiltreRole] = useState<string>('tous');

  // --- Modale 1 : Lien d'invitation sécurisé ---
  const [modaleInvitationOuverte, setModaleInvitationOuverte] = useState(false);
  const [chargementGenInvitation, setChargementGenInvitation] = useState(false);
  const [lienGenere, setLienGenere] = useState<string | null>(null);
  const [lienCopie, setLienCopie] = useState(false);
  const [formInvitation, setFormInvitation] = useState({
    role: 'membre',
    email: '',
    jours_validite: 7,
  });

  // --- Modale 2 : Ajout direct avec génération d'identifiants ---
  const [modaleAjoutOuverte, setModaleAjoutOuverte] = useState(false);
  const [chargementAjout, setChargementAjout] = useState(false);
  const [membreCree, setMembreCree] = useState<{
    nom: string;
    email: string;
    motDePasse: string;
  } | null>(null);
  const [messageCopie, setMessageCopie] = useState(false);
  const [formAjout, setFormAjout] = useState({
    name: '',
    email: '',
    phone: '',
    role: 'membre',
    mot_de_passe_personnalise: '',
  });

  const chargerUtilisateurs = async () => {
    setChargement(true);
    try {
      const rep = await api.get<{ succes: boolean; utilisateurs: Utilisateur[] }>('/utilisateurs');
      if (rep.data.succes) setUtilisateurs(rep.data.utilisateurs);
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerUtilisateurs();
  }, []);

  const basculerStatut = async (id: number) => {
    try {
      const rep = await api.patch(`/utilisateurs/${id}/toggle`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerUtilisateurs();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors du changement de statut.' });
    }
  };

  const supprimerUtilisateur = async (id: number, nom: string) => {
    if (!confirm(`Voulez-vous vraiment supprimer le compte de ${nom} ?`)) return;
    try {
      const rep = await api.delete(`/utilisateurs/${id}`);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: rep.data.message });
        chargerUtilisateurs();
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Impossible de supprimer.' });
    }
  };

  // 1. Action Générer Lien Invitation Sécurisé
  const genererLienInvitation = async (e: React.FormEvent) => {
    e.preventDefault();
    setChargementGenInvitation(true);
    try {
      const rep = await api.post<{ succes: boolean; lien: string }>('/invitations', formInvitation);
      if (rep.data.succes) {
        setLienGenere(rep.data.lien);
      }
    } catch (err: any) {
      alert(err.response?.data?.message || "Erreur lors de la génération du lien d'invitation.");
    } finally {
      setChargementGenInvitation(false);
    }
  };

  const copierLienInvitation = () => {
    if (!lienGenere) return;
    navigator.clipboard.writeText(lienGenere);
    setLienCopie(true);
    setTimeout(() => setLienCopie(false), 2500);
  };

  const partagerWhatsAppInvitation = () => {
    if (!lienGenere) return;
    const tontineNom = currentUser?.tenant?.nom || 'notre Tontine';
    const texte = `Bonjour ! Voici votre lien d'invitation officiel pour rejoindre ${tontineNom} sur Tontine Pro : ${lienGenere}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(texte)}`, '_blank');
  };

  // 2. Action Ajout Direct d'un Membre
  const creerMembreDirect = async (e: React.FormEvent) => {
    e.preventDefault();
    setChargementAjout(true);
    try {
      const rep = await api.post<{
        succes: boolean;
        message: string;
        mot_de_passe_temporaire: string;
        utilisateur: Utilisateur;
      }>('/utilisateurs/creer-membre', formAjout);

      if (rep.data.succes) {
        setMembreCree({
          nom: rep.data.utilisateur.name,
          email: rep.data.utilisateur.email,
          motDePasse: rep.data.mot_de_passe_temporaire,
        });
        chargerUtilisateurs();
      }
    } catch (err: any) {
      alert(err.response?.data?.message || 'Erreur lors de la création du membre.');
    } finally {
      setChargementAjout(false);
    }
  };

  const copierMessageIdentifiants = () => {
    if (!membreCree) return;
    const tontineNom = currentUser?.tenant?.nom || 'notre Tontine';
    const texte = `Bonjour ${membreCree.nom},\nVotre compte d'adhérent a été créé pour ${tontineNom}.\n\nVos identifiants de connexion :\n- Email : ${membreCree.email}\n- Mot de passe temporaire : ${membreCree.motDePasse}\n\nConnectez-vous sur : ${window.location.origin}/connexion`;
    navigator.clipboard.writeText(texte);
    setMessageCopie(true);
    setTimeout(() => setMessageCopie(false), 2500);
  };

  // Filtrage combiné (Recherche textuelle + Statut + Rôle)
  const utilisateursFiltres = useMemo(() => {
    return utilisateurs.filter((u) => {
      const matchRecherche =
        !recherche ||
        u.name.toLowerCase().includes(recherche.toLowerCase()) ||
        u.email.toLowerCase().includes(recherche.toLowerCase()) ||
        (u.phone && u.phone.includes(recherche));

      const matchStatut =
        filtreStatut === 'tous' ||
        (filtreStatut === 'actif' && u.is_active) ||
        (filtreStatut === 'inactif' && !u.is_active);

      const matchRole =
        filtreRole === 'tous' || u.role.toLowerCase() === filtreRole.toLowerCase();

      return matchRecherche && matchStatut && matchRole;
    });
  }, [utilisateurs, recherche, filtreStatut, filtreRole]);

  const optionsFiltresStatut: OptionFiltre[] = [
    { cle: 'tous', label: 'Tous les membres', compte: utilisateurs.length },
    { cle: 'actif', label: 'Membres Actifs', compte: utilisateurs.filter((u) => u.is_active).length },
    { cle: 'inactif', label: 'En attente (Sas)', compte: utilisateurs.filter((u) => !u.is_active).length },
  ];

  return (
    <div className="space-y-6 animate-in fade-in duration-300">
      {/* En-tête de page & Boutons d'Action Clés */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
            Membres & Adhésions Tontine
          </h2>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Générez des liens d'invitation sécurisés ou ajoutez des membres avec leurs accès temporaires
          </p>
        </div>

        {/* Boutons d'Action Administrateur (2 Scénarios) */}
        <div className="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
          {/* Scénario 1 : Génération d'un lien d'invitation sécurisé */}
          <button
            onClick={() => {
              setLienGenere(null);
              setModaleInvitationOuverte(true);
            }}
            className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white dark:bg-sombre-surface text-slate-800 dark:text-white border border-slate-200 dark:border-sombre-bordure hover:bg-slate-50 dark:hover:bg-sombre-survol transition-all shadow-2xs"
          >
            <Link2 className="w-3.5 h-3.5 text-blue-600" />
            <span>Inviter par Lien</span>
          </button>

          {/* Scénario 2 : Ajout direct avec génération d'identifiants */}
          <button
            onClick={() => {
              setMembreCree(null);
              setFormAjout({
                name: '',
                email: '',
                phone: '',
                role: 'membre',
                mot_de_passe_personnalise: '',
              });
              setModaleAjoutOuverte(true);
            }}
            className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition-all"
          >
            <UserPlus className="w-3.5 h-3.5" />
            <span>Ajouter un Membre</span>
          </button>
        </div>
      </div>

      {message && (
        <Alerte variante={message.type === 'succes' ? 'succes' : 'danger'}>
          {message.texte}
        </Alerte>
      )}

      {/* Barre de recherche et de filtres responsive */}
      <BarreFiltresTableau
        termeRecherche={recherche}
        surChangementRecherche={setRecherche}
        placeholder="Rechercher un membre par nom, email, téléphone..."
        optionsFiltres={optionsFiltresStatut}
        filtreActif={filtreStatut}
        surChangementFiltre={setFiltreStatut}
        totalResultats={utilisateursFiltres.length}
        libelleResultats="membre(s) listé(s)"
      />

      {/* Tableau des Membres */}
      <Carte className="p-0 overflow-hidden border border-slate-200/80 dark:border-sombre-bordure shadow-xs">
        {chargement ? (
          <div className="p-6">
            <SqueletteTableau lignes={6} />
          </div>
        ) : (
          <div className="overflow-x-auto">
            {utilisateursFiltres.length === 0 ? (
              <div className="text-center py-12 text-xs text-slate-400 dark:text-slate-500">
                Aucun membre ne correspond à vos critères de recherche.
              </div>
            ) : (
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-slate-100 dark:border-sombre-bordure/60 text-slate-400 dark:text-slate-500 font-semibold tracking-wider uppercase text-[11px] bg-slate-50/50 dark:bg-sombre-surface/50">
                    <th className="py-3.5 px-4 sm:px-6">Membre</th>
                    <th className="py-3.5 px-4">Coordonnées</th>
                    <th className="py-3.5 px-4">Rôle</th>
                    <th className="py-3.5 px-4">Accès Compte</th>
                    <th className="py-3.5 px-4">Inscrit le</th>
                    <th className="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure/40">
                  {utilisateursFiltres.map((u) => {
                    const estMoi = u.id === currentUser?.id;

                    return (
                      <tr
                        key={u.id}
                        className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/40 transition-colors"
                      >
                        {/* Membre */}
                        <td className="py-4 px-4 sm:px-6">
                          <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700/80 font-bold flex items-center justify-center text-xs shrink-0 shadow-2xs">
                              {u.name.charAt(0).toUpperCase()}
                            </div>
                            <div className="min-w-0">
                              <span className="font-bold text-slate-900 dark:text-white block truncate">
                                {u.name}
                              </span>
                              {estMoi && (
                                <span className="text-[10px] text-blue-600 dark:text-blue-400 font-semibold">
                                  (Vous-même)
                                </span>
                              )}
                            </div>
                          </div>
                        </td>

                        {/* Coordonnées */}
                        <td className="py-4 px-4 space-y-0.5 text-slate-500 dark:text-slate-400">
                          <div className="flex items-center gap-1.5">
                            <Mail className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                            <span className="truncate max-w-[160px] sm:max-w-none">{u.email}</span>
                          </div>
                          {u.phone && (
                            <div className="flex items-center gap-1.5 text-[11px]">
                              <Phone className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                              <span>{u.phone}</span>
                            </div>
                          )}
                        </td>

                        {/* Rôle */}
                        <td className="py-4 px-4">
                          <span className="capitalize font-semibold text-slate-700 dark:text-slate-300">
                            {u.role}
                          </span>
                        </td>

                        {/* Statut */}
                        <td className="py-4 px-4 whitespace-nowrap">
                          <Badge variante={u.is_active ? 'succes' : 'alerte'}>
                            {u.is_active ? 'Actif' : 'En attente'}
                          </Badge>
                        </td>

                        {/* Date inscription */}
                        <td className="py-4 px-4 text-slate-400 dark:text-slate-500 whitespace-nowrap">
                          {formaterDate(u.created_at)}
                        </td>

                        {/* Actions */}
                        <td className="py-4 px-4 sm:px-6 text-right space-x-2 whitespace-nowrap">
                          {!estMoi && (
                            <>
                              <Bouton
                                variante={u.is_active ? 'contour' : 'primaire'}
                                taille="sm"
                                onClick={() => basculerStatut(u.id)}
                                icone={
                                  u.is_active ? (
                                    <UserX className="w-3.5 h-3.5" />
                                  ) : (
                                    <UserCheck className="w-3.5 h-3.5" />
                                  )
                                }
                              >
                                {u.is_active ? 'Désactiver' : 'Valider'}
                              </Bouton>

                              <button
                                onClick={() => supprimerUtilisateur(u.id, u.name)}
                                className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                title="Supprimer le membre"
                              >
                                <Trash2 className="w-3.5 h-3.5" />
                              </button>
                            </>
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

      {/* ========================================================================= */}
      {/* MODALE 1 : INVITATION SÉCURISÉE (Scénario 1)                             */}
      {/* ========================================================================= */}
      <Modal
        estOuvert={modaleInvitationOuverte}
        surFermer={() => setModaleInvitationOuverte(false)}
        titre="Générer un Lien d'Invitation Sécurisé"
      >
        <div className="space-y-4">
          <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Ce lien contient une clé cryptographique unique de 64 caractères scellée à votre tontine (<strong>{currentUser?.tenant?.nom}</strong>). 
            Il est impossible pour le destinataire de modifier l'adresse ou d'intégrer une autre tontine.
          </p>

          {!lienGenere ? (
            <form onSubmit={genererLienInvitation} className="space-y-3.5">
              <div>
                <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Rôle attribué à l'adhérent
                </label>
                <select
                  value={formInvitation.role}
                  onChange={(e) => setFormInvitation({ ...formInvitation, role: e.target.value })}
                  className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-800 dark:text-white"
                >
                  <option value="membre">Membre (Adhérent Épargnant)</option>
                  <option value="tresorier">Trésorier (Gestionnaire de caisse)</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Restreindre à un email précis (Facultatif)
                </label>
                <input
                  type="email"
                  placeholder="laisser vide si lien multi-utilisateurs"
                  value={formInvitation.email}
                  onChange={(e) => setFormInvitation({ ...formInvitation, email: e.target.value })}
                  className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-800 dark:text-white placeholder:text-slate-400"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Durée de validité
                </label>
                <select
                  value={formInvitation.jours_validite}
                  onChange={(e) => setFormInvitation({ ...formInvitation, jours_validite: Number(e.target.value) })}
                  className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-800 dark:text-white"
                >
                  <option value={3}>3 jours</option>
                  <option value={7}>7 jours (Recommandé)</option>
                  <option value={15}>15 jours</option>
                  <option value={30}>30 jours</option>
                </select>
              </div>

              <div className="pt-2 flex justify-end gap-2">
                <Bouton
                  type="button"
                  variante="secondaire"
                  taille="sm"
                  onClick={() => setModaleInvitationOuverte(false)}
                >
                  Annuler
                </Bouton>
                <Bouton
                  type="submit"
                  variante="primaire"
                  taille="sm"
                  chargement={chargementGenInvitation}
                  icone={<Sparkles className="w-3.5 h-3.5" />}
                >
                  Générer le lien en 1 clic
                </Bouton>
              </div>
            </form>
          ) : (
            <div className="space-y-4 pt-1 animate-in fade-in">
              <div className="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 text-emerald-800 dark:text-emerald-300 flex items-center gap-2 text-xs font-semibold">
                <Check className="w-4 h-4 text-emerald-600 shrink-0" />
                <span>Lien unique prêt à être partagé !</span>
              </div>

              <div className="p-3 rounded-xl bg-slate-50 dark:bg-sombre-carte border border-slate-200 dark:border-sombre-bordure break-all font-mono text-[11px] text-slate-700 dark:text-slate-300 select-all">
                {lienGenere}
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={copierLienInvitation}
                  className="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl text-xs font-bold bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:bg-slate-800 transition-all shadow-xs"
                >
                  {lienCopie ? <Check className="w-4 h-4 text-emerald-400" /> : <Copy className="w-4 h-4" />}
                  <span>{lienCopie ? 'Lien copié dans le presse-papier !' : 'Copier le lien'}</span>
                </button>

                <button
                  onClick={partagerWhatsAppInvitation}
                  className="inline-flex items-center justify-center gap-1.5 py-2.5 px-3.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-xs"
                  title="Partager sur WhatsApp"
                >
                  <Share2 className="w-4 h-4" />
                  <span className="hidden sm:inline">WhatsApp</span>
                </button>
              </div>

              <div className="pt-2 text-center">
                <button
                  onClick={() => setLienGenere(null)}
                  className="text-xs text-blue-600 hover:underline font-semibold"
                >
                  Générer un autre lien
                </button>
              </div>
            </div>
          )}
        </div>
      </Modal>

      {/* ========================================================================= */}
      {/* MODALE 2 : AJOUT DIRECT D'UN MEMBRE (Scénario 2)                          */}
      {/* ========================================================================= */}
      <Modal
        estOuvert={modaleAjoutOuverte}
        surFermer={() => setModaleAjoutOuverte(false)}
        titre="Ajouter un Membre Directement"
      >
        <div className="space-y-4">
          {!membreCree ? (
            <form onSubmit={creerMembreDirect} className="space-y-3.5">
              <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Le membre sera instantanément rattaché à <strong>{currentUser?.tenant?.nom}</strong> et activé sans délai. 
                Des identifiants d'accès provisoires seront créés et prêts à être copiés.
              </p>

              <ChampTexte
                etiquette="Nom complet"
                type="text"
                required
                placeholder="Ex: Alain Roger Foko"
                value={formAjout.name}
                onChange={(e) => setFormAjout({ ...formAjout, name: e.target.value })}
                iconeGauche={<User className="w-4 h-4" />}
              />

              <ChampTexte
                etiquette="Adresse email"
                type="email"
                required
                placeholder="alain.foko@exemple.cm"
                value={formAjout.email}
                onChange={(e) => setFormAjout({ ...formAjout, email: e.target.value })}
                iconeGauche={<Mail className="w-4 h-4" />}
              />

              <ChampTexte
                etiquette="Numéro de téléphone"
                type="tel"
                required
                placeholder="+237 6XX XX XX XX"
                value={formAjout.phone}
                onChange={(e) => setFormAjout({ ...formAjout, phone: e.target.value })}
                iconeGauche={<Phone className="w-4 h-4" />}
              />

              <div>
                <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Rôle au sein du bureau
                </label>
                <select
                  value={formAjout.role}
                  onChange={(e) => setFormAjout({ ...formAjout, role: e.target.value })}
                  className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-800 dark:text-white"
                >
                  <option value="membre">Membre Adhérent (Épargnant)</option>
                  <option value="tresorier">Trésorier (Caisse & Encaissements)</option>
                  <option value="admin">Administrateur Adjoint</option>
                </select>
              </div>

              <ChampTexte
                etiquette="Mot de passe personnalisé (Optionnel)"
                type="text"
                placeholder="Laisser vide pour générer automatiquement"
                value={formAjout.mot_de_passe_personnalise}
                onChange={(e) => setFormAjout({ ...formAjout, mot_de_passe_personnalise: e.target.value })}
                iconeGauche={<Lock className="w-4 h-4" />}
              />

              <div className="pt-2 flex justify-end gap-2">
                <Bouton
                  type="button"
                  variante="secondaire"
                  taille="sm"
                  onClick={() => setModaleAjoutOuverte(false)}
                >
                  Annuler
                </Bouton>
                <Bouton
                  type="submit"
                  variante="primaire"
                  taille="sm"
                  chargement={chargementAjout}
                  icone={<UserPlus className="w-3.5 h-3.5" />}
                >
                  Créer le membre
                </Bouton>
              </div>
            </form>
          ) : (
            <div className="space-y-4 pt-1 animate-in fade-in">
              <div className="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 text-emerald-800 dark:text-emerald-300 flex items-center gap-2 text-xs font-semibold">
                <Check className="w-4 h-4 text-emerald-600 shrink-0" />
                <span>Compte créé et activé avec succès !</span>
              </div>

              {/* Fiche d'identifiants */}
              <div className="p-4 rounded-xl bg-slate-50 dark:bg-sombre-carte border border-slate-200 dark:border-sombre-bordure space-y-2 text-xs">
                <p className="font-bold text-slate-900 dark:text-white">Identifiants pour {membreCree.nom} :</p>
                <div className="space-y-1 font-mono text-[11px] text-slate-700 dark:text-slate-300">
                  <p>• <strong>Email :</strong> {membreCree.email}</p>
                  <p>• <strong>Mot de passe :</strong> <span className="bg-amber-100 dark:bg-amber-950/80 px-1.5 py-0.5 rounded text-amber-900 dark:text-amber-200 font-bold">{membreCree.motDePasse}</span></p>
                  <p>• <strong>Lien :</strong> {window.location.origin}/connexion</p>
                </div>
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={copierMessageIdentifiants}
                  className="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl text-xs font-bold bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:bg-slate-800 transition-all shadow-xs"
                >
                  {messageCopie ? <Check className="w-4 h-4 text-emerald-400" /> : <Copy className="w-4 h-4" />}
                  <span>{messageCopie ? 'Message copié !' : "Copier le message d'accès"}</span>
                </button>

                <button
                  onClick={() => {
                    const texte = `Bonjour ${membreCree.nom},\nVotre compte d'adhérent a été créé pour ${currentUser?.tenant?.nom || 'notre tontine'}.\n\nVos identifiants :\nEmail: ${membreCree.email}\nMot de passe: ${membreCree.motDePasse}\nLien: ${window.location.origin}/connexion`;
                    window.open(`https://wa.me/?text=${encodeURIComponent(texte)}`, '_blank');
                  }}
                  className="inline-flex items-center justify-center gap-1.5 py-2.5 px-3.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-xs"
                  title="Envoyer via WhatsApp"
                >
                  <Send className="w-4 h-4" />
                  <span className="hidden sm:inline">WhatsApp</span>
                </button>
              </div>

              <div className="pt-2 text-center">
                <button
                  onClick={() => {
                    setMembreCree(null);
                    setModaleAjoutOuverte(false);
                  }}
                  className="text-xs text-slate-500 hover:text-slate-700 font-semibold"
                >
                  Terminer
                </button>
              </div>
            </div>
          )}
        </div>
      </Modal>
    </div>
  );
};

export default GestionMembres;
