import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Bouton } from '../../components/ui/Bouton';
import { Alerte } from '../../components/ui/Alerte';
import { User, Mail, Phone, Lock, UserPlus } from 'lucide-react';

export const Inscription: React.FC = () => {
  const navigate = useNavigate();
  const { inscription, estChargement, erreur, effacerErreur } = useAuthStore();

  const [formulaire, setFormulaire] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
  });

  const handleChange = (champ: string, valeur: string) => {
    setFormulaire((prev) => ({ ...prev, [champ]: valeur }));
  };

  const soumettre = async (e: React.FormEvent) => {
    e.preventDefault();
    effacerErreur();

    try {
      await inscription(formulaire);
      // Redirection immédiate vers le sas d'attente
      navigate('/compte-inactif');
    } catch {
      // Géré par le store
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h3 className="text-lg font-bold text-slate-900 dark:text-white">Adhésion & Inscription</h3>
        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
          Remplissez vos coordonnées pour rejoindre la tontine
        </p>
      </div>

      {erreur && (
        <Alerte variante="danger" titre="Erreur lors de l'enregistrement">
          {erreur}
        </Alerte>
      )}

      <form onSubmit={soumettre} className="space-y-3.5">
        <ChampTexte
          etiquette="Nom complet"
          type="text"
          required
          placeholder="Ex: Jean Paul Nguema"
          value={formulaire.name}
          onChange={(e) => handleChange('name', e.target.value)}
          iconeGauche={<User className="w-4 h-4" />}
        />

        <ChampTexte
          etiquette="Adresse email"
          type="email"
          required
          placeholder="jean.paul@exemple.com"
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

        <p className="text-[11px] text-slate-500 dark:text-slate-400 italic">
          Note : Par mesure de sécurité pour la caisse collective, chaque adhésion est vérifiée et activée par le bureau avant déblocage des accès.
        </p>

        <Bouton
          type="submit"
          variante="primaire"
          chargement={estChargement}
          className="w-full justify-center mt-2"
          icone={<UserPlus className="w-4 h-4 mr-1" />}
        >
          Créer mon compte
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
