import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

let csrfPromesse: Promise<unknown> | null = null;

export const assurerCsrf = async (): Promise<void> => {
  // Récupérer le cookie CSRF Sanctum si non présent ou si promesse en cours
  if (!csrfPromesse) {
    csrfPromesse = axios.get('/sanctum/csrf-cookie', { withCredentials: true })
      .finally(() => {
        csrfPromesse = null;
      });
  }
  await csrfPromesse;
};

// Intercepteur de requête : s'assure que le cookie CSRF est présent pour les requêtes mutantes
api.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
  const methode = config.method?.toLowerCase();
  if (methode && ['post', 'put', 'patch', 'delete'].includes(methode)) {
    // S'assurer que le cookie XSRF-TOKEN est en place
    const aCsrf = document.cookie.includes('XSRF-TOKEN=');
    if (!aCsrf) {
      await assurerCsrf();
    }
  }

  // Si un jeton Bearer est mémorisé, on l'ajoute également en Authorization
  const jeton = localStorage.getItem('jeton_auth');
  if (jeton && config.headers) {
    config.headers.Authorization = `Bearer ${jeton}`;
  }

  return config;
}, (erreur) => {
  return Promise.reject(erreur);
});

// Intercepteur de réponse : intercepte les expirations de session
api.interceptors.response.use(
  (reponse) => reponse,
  (erreur: AxiosError<{ message?: string; statut?: string }>) => {
    if (erreur.response?.status === 401) {
      // Session expirée ou déconnecté
      localStorage.removeItem('jeton_auth');
      if (window.location.pathname !== '/connexion' && window.location.pathname !== '/inscription') {
        window.location.href = '/connexion?session_expiree=1';
      }
    }
    return Promise.reject(erreur);
  }
);

export default api;
