import { create } from 'zustand';
import api, { assurerCsrf } from '../lib/api';
import { Utilisateur } from '../types';

interface EtatAuth {
  utilisateur: Utilisateur | null;
  estConnecte: boolean;
  estActif: boolean;
  estBureau: boolean;
  estChargement: boolean;
  erreur: string | null;

  // Actions
  initialiser: () => Promise<void>;
  connexion: (identifiants: { email: string; password: string; remember?: boolean }) => Promise<Utilisateur>;
  inscription: (donnees: { name: string; email: string; phone: string; password: string; password_confirmation: string }) => Promise<Utilisateur>;
  deconnexion: () => Promise<void>;
  mettreAJourUtilisateur: (utilisateur: Utilisateur) => void;
  effacerErreur: () => void;
}

export const useAuthStore = create<EtatAuth>((set, get) => ({
  utilisateur: null,
  estConnecte: false,
  estActif: false,
  estBureau: false,
  estChargement: true,
  erreur: null,

  initialiser: async () => {
    set({ estChargement: true, erreur: null });
    try {
      const reponse = await api.get<{ succes: boolean; utilisateur: Utilisateur; est_actif: boolean; est_bureau: boolean }>('/utilisateur');
      if (reponse.data.succes && reponse.data.utilisateur) {
        const u = reponse.data.utilisateur;
        set({
          utilisateur: u,
          estConnecte: true,
          estActif: Boolean(u.is_active),
          estBureau: ['admin', 'tresorier'].includes(u.role),
          estChargement: false,
        });
        return;
      }
    } catch {
      // Non connecté
    }
    set({
      utilisateur: null,
      estConnecte: false,
      estActif: false,
      estBureau: false,
      estChargement: false,
    });
  },

  connexion: async ({ email, password, remember }) => {
    set({ estChargement: true, erreur: null });
    try {
      await assurerCsrf();
      const reponse = await api.post<{
        succes: boolean;
        message: string;
        utilisateur: Utilisateur;
        jeton?: string;
        est_actif: boolean;
      }>('/login', { email, password, remember });

      const u = reponse.data.utilisateur;
      if (reponse.data.jeton) {
        localStorage.setItem('jeton_auth', reponse.data.jeton);
      }

      set({
        utilisateur: u,
        estConnecte: true,
        estActif: Boolean(u.is_active),
        estBureau: ['admin', 'tresorier'].includes(u.role),
        estChargement: false,
      });

      return u;
    } catch (err: any) {
      const message = err.response?.data?.message || err.response?.data?.errors?.email?.[0] || 'Identifiants invalides.';
      set({ erreur: message, estChargement: false });
      throw new Error(message);
    }
  },

  inscription: async (donnees) => {
    set({ estChargement: true, erreur: null });
    try {
      await assurerCsrf();
      const reponse = await api.post<{
        succes: boolean;
        message: string;
        utilisateur: Utilisateur;
        jeton?: string;
        est_actif: boolean;
      }>('/register', donnees);

      const u = reponse.data.utilisateur;
      if (reponse.data.jeton) {
        localStorage.setItem('jeton_auth', reponse.data.jeton);
      }

      set({
        utilisateur: u,
        estConnecte: true,
        estActif: Boolean(u.is_active),
        estBureau: ['admin', 'tresorier'].includes(u.role),
        estChargement: false,
      });

      return u;
    } catch (err: any) {
      const message = err.response?.data?.message || 'Erreur lors de l’inscription.';
      set({ erreur: message, estChargement: false });
      throw err;
    }
  },

  deconnexion: async () => {
    try {
      await api.post('/logout');
    } catch {
      // Ignorer l'erreur réseau éventuelle lors du logout
    } finally {
      localStorage.removeItem('jeton_auth');
      set({
        utilisateur: null,
        estConnecte: false,
        estActif: false,
        estBureau: false,
        estChargement: false,
      });
    }
  },

  mettreAJourUtilisateur: (utilisateur: Utilisateur) => {
    set({
      utilisateur,
      estActif: Boolean(utilisateur.is_active),
      estBureau: ['admin', 'tresorier'].includes(utilisateur.role),
    });
  },

  effacerErreur: () => set({ erreur: null }),
}));
