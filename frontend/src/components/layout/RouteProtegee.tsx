import React, { useEffect } from 'react';
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { Loader2 } from 'lucide-react';

export const RouteProtegee: React.FC = () => {
  const { estConnecte, estActif, sessionVerifiee, estChargement, initialiser } = useAuthStore();
  const location = useLocation();

  useEffect(() => {
    if (!sessionVerifiee) {
      initialiser();
    }
  }, [sessionVerifiee, initialiser]);

  if (!sessionVerifiee || estChargement) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-slate-50 dark:bg-sombre-fond text-slate-800 dark:text-sombre-texte">
        <Loader2 className="w-10 h-10 animate-spin text-primaire-600 mb-3" />
        <p className="text-sm font-medium text-slate-500 dark:text-slate-400">Chargement de votre session...</p>
      </div>
    );
  }

  if (!estConnecte) {
    return <Navigate to="/connexion" state={{ from: location }} replace />;
  }

  // Si le compte est inactif et que l'utilisateur n'est pas déjà sur la page d'inactivité
  if (!estActif && location.pathname !== '/compte-inactif') {
    return <Navigate to="/compte-inactif" replace />;
  }

  // Si le compte est actif mais que l'utilisateur tente d'aller sur /compte-inactif
  if (estActif && location.pathname === '/compte-inactif') {
    return <Navigate to="/tableau-de-bord" replace />;
  }

  return <Outlet />;
};
