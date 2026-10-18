import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Bouton } from '../../components/ui/Bouton';
import { Alerte } from '../../components/ui/Alerte';
import { User, Mail, Phone, Lock, UserPlus, Building2, Sparkles, CheckCircle2 } from 'lucide-react';
import api from '../../lib/api';

interface TontinePublique {
  id: number;
  nom: string;
  slug: string;
  devise: string;
}

export const Inscription: React.FC = () => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { inscription, erreur, effacerErreur } = useAuthStore();
  const [chargement, setChargement] = useState(false);

  // Liste des tontines disponibles pour l'adhésion
  const [tontines, setTontines] = useState<TontinePublique[]>([]);
  const [modeInscription, setModeInscription] = useState<'rejoindre' | 'creer'>('rejoindre');

  const [formulaire, setFormulaire] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    tenant_id: '',
    tenant_slug: '',
    nom_nouvelle_tontine: '',
  });

  useEffect(() => {
    const chargerTontines = async () => {
      try {
        const res = await api.get<{ succes: boolean; tontines: TontinePublique[] }>('/tontines-publiques');
        if (res.data.succes && res.data.tontines.length > 0) {
          setTontines(res.data.tontines);

          // Si un slug de tontine est passé dans l'URL (?tontine=slug)
          const slugUrl = searchParams.get('tontine');
          if (slugUrl) {
            const trouve = res.data.tontines.find((t) => t.slug === slugUrl);
            if (trouve) {
              setFormulaire((prev) => ({ ...prev, tenant_id: String(trouve.id), tenant_slug: trouve.slug }));
              return;
            }
          }

          // Par défaut, première tontine
          setFormulaire((prev) => ({
            ...prev,
            tenant_id: String(res.data.tontines[0].id),
            tenant_slug: res.data.tontines[0].slug,
          }));
        }
      } catch {
        // Ignorer en mode hors ligne
      }
    };

    chargerTontines();
  }, [searchParams]);

  const handleChange = (champ: string, valeur: string) => {
    setFormulaire((prev) => ({ ...prev, [champ]: valeur }));
  };

  const soumettre = async (e: React.FormEvent) => {
    e.preventDefault();
    effacerErreur();
    setChargement(true);

    try {
      const payload: any = {
        name: formulaire.name,
        email: formulaire.email,
        phone: formulaire.phone,
        password: formulaire.password,
        password_confirmation: formulaire.password_confirmation,
      };

      if (modeInscription === 'creer') {
        payload.creer_nouvelle_tontine = true;
        payload.nom_nouvelle_tontine = formulaire.nom_nouvelle_tontine;
      } else {
        if (formulaire.tenant_id) payload.tenant_id = Number(formulaire.tenant_id);
        if (formulaire.tenant_slug) payload.tenant_slug = formulaire.tenant_slug;
      }

      const user = await inscription(payload);

      // Si c'est un créateur de tontine -> Actif immédiatement -> Tableau de bord
      // Si c'est un membre rejoignant une tontine -> Sas d'attente d'approbation
      if (user.is_active) {
        navigate('/tableau-de-bord');
      } else {
        navigate('/compte-inactif');
      }
    } catch {
      // Erreur gérée par le store
    } finally {
      setChargement(false);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h3 className="text-lg font-bold text-slate-900 dark:text-white">Inscription & Adhésion</h3>
        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
          {modeInscription === 'rejoindre'
            ? 'Rejoignez votre tontine pour suivre vos cotisations et vos prêts'
            : 'Créez votre propre organisation tontinière en quelques secondes'}
        </p>
      </div>

      {erreur && (
        <Alerte variante="danger" titre="Erreur lors de l'enregistrement">
          {erreur}
        </Alerte>
      )}

      {/* Onglets de choix : Rejoindre une tontine OU Créer sa tontine */}
      <div className="grid grid-cols-2 p-1 bg-slate-100 dark:bg-sombre-surface rounded-xl text-xs font-bold">
        <button
          type="button"
          onClick={() => setModeInscription('rejoindre')}
          className={`py-2 rounded-lg transition-all ${
            modeInscription === 'rejoindre'
              ? 'bg-white dark:bg-sombre-carte text-slate-900 dark:text-white shadow-xs'
              : 'text-slate-500 hover:text-slate-800'
          }`}
        >
          Rejoindre une Tontine
        </button>
        <button
          type="button"
          onClick={() => setModeInscription('creer')}
          className={`py-2 rounded-lg transition-all flex items-center justify-center gap-1.5 ${
            modeInscription === 'creer'
              ? 'bg-white dark:bg-sombre-carte text-blue-600 dark:text-blue-400 shadow-xs'
              : 'text-slate-500 hover:text-slate-800'
          }`}
        >
          <Sparkles className="w-3.5 h-3.5" />
          <span>Créer une Tontine</span>
        </button>
      </div>

      <form onSubmit={soumettre} className="space-y-3.5">
        {/* Choix ou création de la Tontine */}
        {modeInscription === 'rejoindre' ? (
          <div>
            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Sélectionnez votre Tontine
            </label>
            <div className="relative">
              <Building2 className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
              <select
                value={formulaire.tenant_id}
                onChange={(e) => {
                  const id = e.target.value;
                  const item = tontines.find((t) => String(t.id) === id);
                  setFormulaire((prev) => ({
                    ...prev,
                    tenant_id: id,
                    tenant_slug: item?.slug || '',
                  }));
                }}
                required
                className="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                {tontines.map((t) => (
                  <option key={t.id} value={t.id}>
                    {t.nom} (@{t.slug})
                  </option>
                ))}
              </select>
            </div>
            <p className="text-[10px] text-slate-400 dark:text-slate-500 mt-1">
              Votre dossier d'adhésion sera transmis au bureau de cette tontine pour validation.
            </p>
          </div>
        ) : (
          <div>
            <ChampTexte
              etiquette="Nom de votre Tontine ou Association"
              type="text"
              required
              placeholder="Ex: Tontine Fraternelle de Douala"
              value={formulaire.nom_nouvelle_tontine}
              onChange={(e) => handleChange('nom_nouvelle_tontine', e.target.value)}
              iconeGauche={<Building2 className="w-4 h-4" />}
            />
            <p className="text-[10px] text-slate-400 dark:text-slate-500 mt-1">
              Vous serez automatiquement configuré comme <strong>Président (Administrateur)</strong> de cette nouvelle tontine.
            </p>
          </div>
        )}

        {/* Coordonnées personnelles */}
        <ChampTexte
          etiquette="Nom complet"
          type="text"
          required
          placeholder="Ex: Paul Alain Ndongo"
          value={formulaire.name}
          onChange={(e) => handleChange('name', e.target.value)}
          iconeGauche={<User className="w-4 h-4" />}
        />

        <ChampTexte
          etiquette="Adresse email"
          type="email"
          required
          placeholder="paul.ndongo@exemple.cm"
          value={formulaire.email}
          onChange={(e) => handleChange('email', e.target.value)}
          iconeGauche={<Mail className="w-4 h-4" />}
        />

        <ChampTexte
          etiquette="Numéro de téléphone"
          type="tel"
          required
          placeholder="+237 6XX XX XX XX"
          value={formulaire.phone}
          onChange={(e) => handleChange('phone', e.target.value)}
          iconeGauche={<Phone className="w-4 h-4" />}
        />

        <ChampTexte
          etiquette="Mot de passe"
          type="password"
          required
          placeholder="Minimum 8 caractères"
          value={formulaire.password}
          onChange={(e) => handleChange('password', e.target.value)}
          iconeGauche={<Lock className="w-4 h-4" />}
        />

        <ChampTexte
          etiquette="Confirmer le mot de passe"
          type="password"
          required
          placeholder="Retapez le mot de passe"
          value={formulaire.password_confirmation}
          onChange={(e) => handleChange('password_confirmation', e.target.value)}
          iconeGauche={<Lock className="w-4 h-4" />}
        />

        <Bouton
          type="submit"
          variante="primaire"
          chargement={chargement}
          className="w-full justify-center mt-3"
          icone={<UserPlus className="w-4 h-4 mr-1" />}
        >
          {modeInscription === 'creer' ? 'Créer ma Tontine & mon Compte' : 'Transmettre mon Adhésion'}
        </Bouton>
      </form>

      <div className="pt-4 border-t border-slate-100 dark:border-sombre-bordure text-center text-xs text-slate-500 dark:text-slate-400">
        Vous avez déjà un compte ?{' '}
        <Link
          to="/connexion"
          className="font-bold text-primaire-600 dark:text-primaire-400 hover:underline"
        >
          Se connecter
        </Link>
      </div>
    </div>
  );
};

export default Inscription;
