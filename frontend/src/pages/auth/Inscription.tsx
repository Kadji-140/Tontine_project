import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Bouton } from '../../components/ui/Bouton';
import { Alerte } from '../../components/ui/Alerte';
import { 
  User, 
  Mail, 
  Phone, 
  Lock, 
  UserPlus, 
  Building2, 
  ShieldCheck, 
  AlertTriangle,
  Sparkles,
  CheckCircle2,
  Clock
} from 'lucide-react';
import api from '../../lib/api';

interface InfosInvitation {
  valide: boolean;
  tontine: {
    nom: string;
    slug: string;
    devise: string;
  };
  role: string;
  email_restreint?: string | null;
  expire_le: string;
}

export const Inscription: React.FC = () => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { inscription, erreur, effacerErreur } = useAuthStore();
  
  const tokenInvitation = searchParams.get('invitation');
  const [chargementInvitation, setChargementInvitation] = useState(Boolean(tokenInvitation));
  const [infosInvitation, setInfosInvitation] = useState<InfosInvitation | null>(null);
  const [erreurInvitation, setErreurInvitation] = useState<string | null>(null);

  const [chargement, setChargement] = useState(false);
  const [formulaire, setFormulaire] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    nom_nouvelle_tontine: '',
  });

  // 1. Vérification du token d'invitation si présent dans l'URL
  useEffect(() => {
    if (!tokenInvitation) {
      setChargementInvitation(false);
      return;
    }

    const verifierToken = async () => {
      setChargementInvitation(true);
      setErreurInvitation(null);
      try {
        const rep = await api.get<{ succes: boolean } & InfosInvitation>(`/invitations/verifier/${tokenInvitation}`);
        if (rep.data.succes && rep.data.valide) {
          setInfosInvitation(rep.data);
          if (rep.data.email_restreint) {
            setFormulaire((prev) => ({ ...prev, email: rep.data.email_restreint! }));
          }
        }
      } catch (err: any) {
        setErreurInvitation(
          err.response?.data?.message ||
          "Ce lien d'invitation est invalide, a déjà été utilisé ou a expiré."
        );
      } finally {
        setChargementInvitation(false);
      }
    };

    verifierToken();
  }, [tokenInvitation]);

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

      if (tokenInvitation && infosInvitation) {
        // Mode Adhésion Sécurisée par Invitation
        payload.invitation_token = tokenInvitation;
      } else {
        // Mode Création Fondateur SaaS
        payload.creer_nouvelle_tontine = true;
        payload.nom_nouvelle_tontine = formulaire.nom_nouvelle_tontine;
      }

      const user = await inscription(payload);

      if (user.is_active) {
        navigate('/tableau-de-bord');
      } else {
        navigate('/compte-inactif');
      }
    } catch {
      // Erreur gérée dans le store
    } finally {
      setChargement(false);
    }
  };

  if (chargementInvitation) {
    return (
      <div className="py-12 text-center space-y-3">
        <div className="w-8 h-8 border-3 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto" />
        <p className="text-xs text-slate-500">Vérification de l'invitation sécurisée...</p>
      </div>
    );
  }

  // Si le token dans l'URL est invalide ou corrompu
  if (tokenInvitation && erreurInvitation) {
    return (
      <div className="space-y-6 text-center">
        <div className="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 mx-auto flex items-center justify-center">
          <AlertTriangle className="w-8 h-8" />
        </div>
        <div className="space-y-2">
          <h3 className="text-lg font-bold text-slate-900 dark:text-white">Lien d'invitation non valide</h3>
          <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
            {erreurInvitation}
          </p>
        </div>
        <div className="p-4 rounded-xl bg-slate-50 dark:bg-sombre-surface text-xs text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-sombre-bordure text-left">
          <p className="font-semibold mb-1">Que pouvez-vous faire ?</p>
          <ul className="list-disc list-inside space-y-1 text-[11px]">
            <li>Demandez à l'administrateur de votre tontine de générer un nouveau lien d'invitation.</li>
            <li>Si vous êtes président et souhaitez créer votre propre tontine,{' '}
              <Link to="/inscription" className="text-blue-600 font-bold hover:underline">
                cliquez ici
              </Link>.
            </li>
          </ul>
        </div>
        <div>
          <Link to="/connexion">
            <Bouton variante="secondaire" taille="sm">Retour à la connexion</Bouton>
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* En-tête conditionnel selon le mode */}
      {infosInvitation ? (
        <div className="space-y-2">
          <div className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60">
            <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
            <span>Invitation Officielle Vérifiée</span>
          </div>
          <h3 className="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            Rejoindre {infosInvitation.tontine.nom}
          </h3>
          <p className="text-xs text-slate-500 dark:text-slate-400">
            Vous avez été invité(e) par le bureau à rejoindre cette organisation. Complétez vos informations pour finaliser votre accès.
          </p>
        </div>
      ) : (
        <div>
          <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60 mb-2">
            <Sparkles className="w-3 h-3 text-blue-600" />
            Nouveau Cycle SaaS
          </div>
          <h3 className="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            Créer votre Tontine
          </h3>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Ouvrez votre organisation tontinière en ligne, créez vos cycles d'épargne et invitez vos membres en 1 clic.
          </p>
        </div>
      )}

      {erreur && (
        <Alerte variante="danger" titre="Erreur lors de l'enregistrement">
          {erreur}
        </Alerte>
      )}

      <form onSubmit={soumettre} className="space-y-3.5">
        {/* Si c'est une création de tontine, demander le nom de l'association */}
        {!infosInvitation && (
          <div className="pb-1">
            <ChampTexte
              etiquette="Nom de votre Tontine ou Association"
              type="text"
              required
              placeholder="Ex: Tontine Fraternelle de Douala"
              value={formulaire.nom_nouvelle_tontine}
              onChange={(e) => handleChange('nom_nouvelle_tontine', e.target.value)}
              iconeGauche={<Building2 className="w-4 h-4 text-blue-600" />}
            />
            <p className="text-[10px] text-slate-400 dark:text-slate-500 mt-1">
              Vous serez automatiquement désigné(e) comme <strong>Président (Administrateur)</strong> de cette tontine.
            </p>
          </div>
        )}

        {/* Informations personnelles */}
        <ChampTexte
          etiquette="Nom complet"
          type="text"
          required
          placeholder="Ex: Jean Paul Ndongo"
          value={formulaire.name}
          onChange={(e) => handleChange('name', e.target.value)}
          iconeGauche={<User className="w-4 h-4" />}
        />

        <ChampTexte
          etiquette="Adresse email"
          type="email"
          required
          placeholder="jean.paul@exemple.cm"
          value={formulaire.email}
          onChange={(e) => handleChange('email', e.target.value)}
          readOnly={Boolean(infosInvitation?.email_restreint)}
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

        {!infosInvitation && (
          <div className="p-3 rounded-xl bg-slate-50 dark:bg-sombre-surface border border-slate-200/70 dark:border-sombre-bordure text-[11px] text-slate-500 dark:text-slate-400">
            <strong>Vous êtes déjà membre d'une tontine ?</strong><br />
            Demandez à votre président de vous transmettre votre lien d'invitation personnel ou vos identifiants d'accès.
          </div>
        )}

        <Bouton
          type="submit"
          variante="primaire"
          chargement={chargement}
          className="w-full justify-center mt-3"
          icone={<UserPlus className="w-4 h-4 mr-1" />}
        >
          {infosInvitation ? 'Rejoindre la Tontine' : 'Créer ma Tontine & Mon Compte'}
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
