import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Bouton } from '../../components/ui/Bouton';
import { Alerte } from '../../components/ui/Alerte';
import { Mail, Lock, ArrowRight } from 'lucide-react';

export const Connexion: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const { connexion, estConnecte, estActif, erreur, effacerErreur } = useAuthStore();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [chargement, setChargement] = useState(false);

  // Si l'utilisateur est déjà connecté, le rediriger
  React.useEffect(() => {
    if (estConnecte) {
      navigate(estActif ? '/tableau-de-bord' : '/compte-inactif', { replace: true });
    }
  }, [estConnecte, estActif, navigate]);

  const soumettre = async (e: React.FormEvent) => {
    e.preventDefault();
    effacerErreur();
    setChargement(true);

    try {
      const user = await connexion({ email, password, remember });
      if (!user.is_active) {
        navigate('/compte-inactif');
      } else {
        const destination = (location.state as any)?.from?.pathname || '/tableau-de-bord';
        navigate(destination, { replace: true });
      }
    } catch {
      // Erreur affichée via le store
    } finally {
      setChargement(false);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h3 className="text-lg font-bold text-slate-900 dark:text-white">Connexion</h3>
        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
          Accédez à votre compte pour suivre vos cotisations et vos prêts
        </p>
      </div>

      {erreur && (
        <Alerte variante="danger" titre="Erreur d'authentification">
          {erreur}
        </Alerte>
      )}

      <form onSubmit={soumettre} className="space-y-4">
        <ChampTexte
          etiquette="Adresse email"
          type="email"
          required
          autoComplete="email"
          placeholder="votre.email@domaine.com"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          iconeGauche={<Mail className="w-4 h-4" />}
        />

        <ChampTexte
          etiquette="Mot de passe"
          type="password"
          required
          autoComplete="current-password"
          placeholder="••••••••"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          iconeGauche={<Lock className="w-4 h-4" />}
        />

        <div className="flex items-center justify-between text-xs">
          <label className="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400">
            <input
              type="checkbox"
              checked={remember}
              onChange={(e) => setRemember(e.target.checked)}
              className="rounded border-slate-300 dark:border-sombre-bordure text-primaire-600 focus:ring-primaire-500"
            />
            <span>Se souvenir de moi</span>
          </label>

          <span className="text-slate-400 dark:text-slate-500">Mot de passe oublié ?</span>
        </div>

        <Bouton
          type="submit"
          variante="primaire"
          chargement={chargement}
          className="w-full justify-center"
          icone={<ArrowRight className="w-4 h-4 ml-1" />}
        >
          Se connecter
        </Bouton>
      </form>

      <div className="pt-4 border-t border-slate-100 dark:border-sombre-bordure text-center text-xs text-slate-500 dark:text-slate-400">
        Pas encore membre ?{' '}
        <Link
          to="/inscription"
          className="font-bold text-primaire-600 dark:text-primaire-400 hover:underline"
        >
          Créer un compte
        </Link>
      </div>
    </div>
  );
};
