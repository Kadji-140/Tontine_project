import React, { useEffect, useState } from 'react';
import { Menu, Bell } from 'lucide-react';
import { SelecteurTheme } from '../ui/SelecteurTheme';
import { useAuthStore } from '../../stores/authStore';
import api from '../../lib/api';
import { Annonce } from '../../types';

interface PropsEnTete {
  surOuvrirMobile?: () => void;
}

export const EnTete: React.FC<PropsEnTete> = ({ surOuvrirMobile }) => {
  const { utilisateur } = useAuthStore();
  const [annonces, setAnnonces] = useState<Annonce[]>([]);
  const [menuAnnoncesOuvert, setMenuAnnoncesOuvert] = useState(false);

  useEffect(() => {
    const chargerAnnonces = async () => {
      try {
        const reponse = await api.get<{ succes: boolean; annonces: Annonce[] }>('/annonces');
        if (reponse.data.succes) {
          setAnnonces(reponse.data.annonces);
        }
      } catch {
        // Ignorer si non dispo
      }
    };

    chargerAnnonces();
  }, []);

  const annoncesNonLues = annonces.filter((a) => !a.est_lue).length;

  const marquerLue = async (id: number) => {
    try {
      await api.post(`/annonces/${id}/lue`);
      setAnnonces((prev) =>
        prev.map((a) => (a.id === id ? { ...a, est_lue: true } : a))
      );
    } catch {
      // Ignorer
    }
  };

  return (
    <header className="sticky top-0 z-30 flex items-center justify-between h-20 px-6 bg-white/80 dark:bg-sombre-surface/80 backdrop-blur-md border-b border-slate-200/80 dark:border-sombre-bordure transition-colors">
      {/* Bouton mobile + Titre */}
      <div className="flex items-center gap-4">
        <button
          onClick={surOuvrirMobile}
          className="p-2 -ml-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-sombre-survol lg:hidden"
        >
          <Menu className="w-6 h-6" />
        </button>

        <div>
          <h1 className="text-base font-bold text-slate-900 dark:text-white">
            Bonjour, <span className="text-primaire-600 dark:text-primaire-400">{utilisateur?.name}</span>
          </h1>
          <p className="text-xs text-slate-500 dark:text-slate-400 hidden sm:block">
            Bienvenue sur votre espace de gestion solidaire
          </p>
        </div>
      </div>

      {/* Actions à droite : Thème + Notifications */}
      <div className="flex items-center gap-3">
        {/* Sélecteur de Thème Dark Mode */}
        <SelecteurTheme compact={false} />

        {/* Notifications / Annonces */}
        <div className="relative">
          <button
            onClick={() => setMenuAnnoncesOuvert(!menuAnnoncesOuvert)}
            aria-label="Annonces et notifications"
            className="relative p-2.5 rounded-xl border border-slate-200 dark:border-sombre-bordure bg-slate-50 dark:bg-sombre-carte hover:bg-slate-100 dark:hover:bg-sombre-survol text-slate-600 dark:text-slate-300 transition-colors"
          >
            <Bell className="w-5 h-5" />
            {annoncesNonLues > 0 && (
              <span className="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-sombre-surface">
                {annoncesNonLues}
              </span>
            )}
          </button>

          {/* Tiroir d'annonces */}
          {menuAnnoncesOuvert && (
            <div className="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white dark:bg-sombre-carte border border-slate-200 dark:border-sombre-bordure shadow-xl p-4 z-50 animate-in fade-in slide-in-from-top-2 duration-150">
              <div className="flex items-center justify-between pb-3 mb-2 border-b border-slate-100 dark:border-sombre-bordure">
                <h4 className="text-sm font-bold text-slate-900 dark:text-white">
                  Annonces & Notifications
                </h4>
                <span className="text-xs font-semibold text-primaire-600 dark:text-primaire-400">
                  {annoncesNonLues} non lue{annoncesNonLues > 1 ? 's' : ''}
                </span>
              </div>

              <div className="max-h-80 overflow-y-auto space-y-2.5">
                {annonces.length === 0 ? (
                  <p className="text-xs text-center py-6 text-slate-400">
                    Aucune annonce pour le moment.
                  </p>
                ) : (
                  annonces.map((annonce) => (
                    <div
                      key={annonce.id}
                      onClick={() => !annonce.est_lue && marquerLue(annonce.id)}
                      className={`p-3 rounded-xl border text-xs transition-colors cursor-pointer ${
                        annonce.est_lue
                          ? 'bg-slate-50/60 dark:bg-sombre-surface/40 border-slate-100 dark:border-sombre-bordure/60 text-slate-500 dark:text-slate-400'
                          : 'bg-primaire-50/40 dark:bg-primaire-950/20 border-primaire-200 dark:border-primaire-800 text-slate-800 dark:text-slate-200 font-medium'
                      }`}
                    >
                      <div className="flex justify-between items-start gap-2 mb-1">
                        <span className="font-bold">{annonce.titre}</span>
                        <span className="text-[10px] text-slate-400 whitespace-nowrap">
                          {annonce.created_at}
                        </span>
                      </div>
                      <p className="line-clamp-2 leading-relaxed">{annonce.message}</p>
                    </div>
                  ))
                )}
              </div>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};
