import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { ShieldAlert } from 'lucide-react';
import { Bouton } from '../ui/Bouton';
import { Link } from 'react-router-dom';

export const RouteBureau: React.FC = () => {
  const { estBureau } = useAuthStore();

  if (!estBureau) {
    return (
      <div className="p-8 max-w-lg mx-auto text-center space-y-4">
        <div className="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 mx-auto flex items-center justify-center">
          <ShieldAlert className="w-8 h-8" />
        </div>
        <h2 className="text-xl font-bold text-slate-900 dark:text-white">Accès Réservé au Bureau</h2>
        <p className="text-sm text-slate-600 dark:text-slate-400">
          Cette section nécessite des privilèges d'administrateur ou de trésorier.
        </p>
        <div className="pt-2">
          <Link to="/tableau-de-bord">
            <Bouton variante="primaire">Retourner au Tableau de Bord</Bouton>
          </Link>
        </div>
      </div>
    );
  }

  return <Outlet />;
};
