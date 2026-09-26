import React, { useState } from 'react';
import { Carte, EnteteCarte, TitreCarte, SousTitreCarte } from '../../components/ui/Carte';
import { Bouton } from '../../components/ui/Bouton';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Alerte } from '../../components/ui/Alerte';
import { useAuthStore } from '../../stores/authStore';
import api from '../../lib/api';
import { User, Mail, Phone, Lock, Save, Shield, Briefcase, MapPin, CreditCard } from 'lucide-react';
import { SelecteurTheme } from '../../components/ui/SelecteurTheme';

export const EditerProfil: React.FC = () => {
  const { utilisateur, mettreAJourUtilisateur } = useAuthStore();

  const [formInfos, setFormInfos] = useState({
    name: utilisateur?.name || '',
    email: utilisateur?.email || '',
    phone: utilisateur?.phone || '',
    profession: utilisateur?.profession || '',
    adresse: utilisateur?.adresse || '',
    cni: utilisateur?.cni || '',
    beneficiaire: utilisateur?.beneficiaire || '',
  });

  const [formMdp, setFormMdp] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  const [message, setMessage] = useState<{ type: 'succes' | 'danger'; texte: string } | null>(null);
  const [chargementInfos, setChargementInfos] = useState(false);
  const [chargementMdp, setChargementMdp] = useState(false);

  const soumettreInfos = async (e: React.FormEvent) => {
    e.preventDefault();
    setChargementInfos(true);
    setMessage(null);

    try {
      const rep = await api.post('/profil', formInfos);
      if (rep.data.succes) {
        mettreAJourUtilisateur(rep.data.utilisateur);
        setMessage({ type: 'succes', texte: 'Informations mises à jour avec succès.' });
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors de la mise à jour.' });
    } finally {
      setChargementInfos(false);
    }
  };

  const soumettreMdp = async (e: React.FormEvent) => {
    e.preventDefault();
    setChargementMdp(true);
    setMessage(null);

    try {
      const rep = await api.put('/profil/mot-de-passe', formMdp);
      if (rep.data.succes) {
        setMessage({ type: 'succes', texte: 'Mot de passe modifié avec succès.' });
        setFormMdp({ current_password: '', password: '', password_confirmation: '' });
      }
    } catch (err: any) {
      setMessage({ type: 'danger', texte: err.response?.data?.message || 'Erreur lors du changement de mot de passe.' });
    } finally {
      setChargementMdp(false);
    }
  };

  return (
    <div className="space-y-6 max-w-4xl mx-auto">
      <div>
        <h2 className="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
          Mon Compte & Paramètres
        </h2>
        <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Gérez vos informations personnelles, vos coordonnées et vos préférences
        </p>
      </div>

      {message && (
        <Alerte variante={message.type === 'succes' ? 'succes' : 'danger'}>
          {message.texte}
        </Alerte>
      )}

      {/* Préférences d'apparence / Dark Mode */}
      <Carte>
        <EnteteCarte>
          <div>
            <TitreCarte>Apparence de l'application</TitreCarte>
            <SousTitreCarte>Basculez entre le mode clair, sombre ou l'adaptation automatique</SousTitreCarte>
          </div>
          <SelecteurTheme compact={false} />
        </EnteteCarte>
      </Carte>

      {/* Informations personnelles */}
      <Carte>
        <EnteteCarte>
          <div>
            <TitreCarte>Informations Personnelles</TitreCarte>
            <SousTitreCarte>Coordonnées utilisées pour vos parts et notifications</SousTitreCarte>
          </div>
        </EnteteCarte>

        <form onSubmit={soumettreInfos} className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <ChampTexte
              etiquette="Nom complet"
              required
              value={formInfos.name}
              onChange={(e) => setFormInfos({ ...formInfos, name: e.target.value })}
              iconeGauche={<User className="w-4 h-4" />}
            />

            <ChampTexte
              etiquette="Adresse email"
              type="email"
              required
              value={formInfos.email}
              onChange={(e) => setFormInfos({ ...formInfos, email: e.target.value })}
              iconeGauche={<Mail className="w-4 h-4" />}
            />

            <ChampTexte
              etiquette="Téléphone (WhatsApp)"
              type="tel"
              required
              value={formInfos.phone}
              onChange={(e) => setFormInfos({ ...formInfos, phone: e.target.value })}
              iconeGauche={<Phone className="w-4 h-4" />}
            />

            <ChampTexte
              etiquette="Profession"
              value={formInfos.profession}
              onChange={(e) => setFormInfos({ ...formInfos, profession: e.target.value })}
              iconeGauche={<Briefcase className="w-4 h-4" />}
            />

            <ChampTexte
              etiquette="Adresse / Ville"
              value={formInfos.adresse}
              onChange={(e) => setFormInfos({ ...formInfos, adresse: e.target.value })}
              iconeGauche={<MapPin className="w-4 h-4" />}
            />

            <ChampTexte
              etiquette="Numéro CNI"
              value={formInfos.cni}
              onChange={(e) => setFormInfos({ ...formInfos, cni: e.target.value })}
              iconeGauche={<CreditCard className="w-4 h-4" />}
            />
          </div>

          <ChampTexte
            etiquette="Bénéficiaire en cas d'empêchement"
            placeholder="Nom et contact de la personne à contacter"
            value={formInfos.beneficiaire}
            onChange={(e) => setFormInfos({ ...formInfos, beneficiaire: e.target.value })}
          />

          <div className="pt-2 flex justify-end">
            <Bouton type="submit" variante="primaire" chargement={chargementInfos} icone={<Save className="w-4 h-4 mr-1" />}>
              Enregistrer les modifications
            </Bouton>
          </div>
        </form>
      </Carte>

      {/* Sécurité / Mot de passe */}
      <Carte>
        <EnteteCarte>
          <div>
            <TitreCarte>Sécurité du Compte</TitreCarte>
            <SousTitreCarte>Modifiez votre mot de passe d'accès</SousTitreCarte>
          </div>
          <Shield className="w-5 h-5 text-slate-400" />
        </EnteteCarte>

        <form onSubmit={soumettreMdp} className="space-y-4 max-w-lg">
          <ChampTexte
            etiquette="Mot de passe actuel"
            type="password"
            required
            value={formMdp.current_password}
            onChange={(e) => setFormMdp({ ...formMdp, current_password: e.target.value })}
            iconeGauche={<Lock className="w-4 h-4" />}
          />

          <ChampTexte
            etiquette="Nouveau mot de passe"
            type="password"
            required
            value={formMdp.password}
            onChange={(e) => setFormMdp({ ...formMdp, password: e.target.value })}
            iconeGauche={<Lock className="w-4 h-4" />}
          />

          <ChampTexte
            etiquette="Confirmer le nouveau mot de passe"
            type="password"
            required
            value={formMdp.password_confirmation}
            onChange={(e) => setFormMdp({ ...formMdp, password_confirmation: e.target.value })}
            iconeGauche={<Lock className="w-4 h-4" />}
          />

          <div className="pt-2">
            <Bouton type="submit" variante="contour" chargement={chargementMdp}>
              Mettre à jour le mot de passe
            </Bouton>
          </div>
        </form>
      </Carte>
    </div>
  );
};
