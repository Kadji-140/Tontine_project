import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { RouteProtegee } from './components/layout/RouteProtegee';
import { RouteBureau } from './components/layout/RouteBureau';
import { RouteSuperAdmin } from './components/layout/RouteSuperAdmin';
import { LayoutInvite } from './components/layout/LayoutInvite';
import { LayoutModerne } from './components/layout/LayoutModerne';

// Pages Publiques / Invités
import { Connexion } from './pages/auth/Connexion';
import { Inscription } from './pages/auth/Inscription';

// Page Compte Inactif (Sas d'attente)
import { CompteInactif } from './pages/auth/CompteInactif';

// Pages Application & Espace Membre
import { TableauDeBord } from './pages/dashboard/TableauDeBord';
import { ListePrets } from './pages/prets/ListePrets';
import { HistoriqueGains } from './pages/gains/HistoriqueGains';
import { ListeCycles } from './pages/cycles/ListeCycles';
import { EditerProfil } from './pages/profil/EditerProfil';

// Pages Gestion Bureau (Admin & Trésorier)
import { ListeSeances } from './pages/seances/ListeSeances';
import { DetailSeance } from './pages/seances/DetailSeance';
import { ListeSanctions } from './pages/sanctions/ListeSanctions';
import { GestionMembres } from './pages/utilisateurs/GestionMembres';

// Pages Supervision SaaS (Super-Admin)
import { TableauDeBordSaaS } from './pages/superadmin/TableauDeBordSaaS';
import { GestionTenants } from './pages/superadmin/GestionTenants';
import { ListeUtilisateursGlobal } from './pages/superadmin/ListeUtilisateursGlobal';

export const App: React.FC = () => {
  return (
    <Routes>
      {/* 1. Routes Publiques (Invité) */}
      <Route element={<LayoutInvite />}>
        <Route path="/connexion" element={<Connexion />} />
        <Route path="/inscription" element={<Inscription />} />
      </Route>

      {/* 2. Routes Protégées (Authentification requise) */}
      <Route element={<RouteProtegee />}>
        {/* Sas d'attente pour comptes en cours de validation */}
        <Route path="/compte-inactif" element={<CompteInactif />} />

        {/* Environnement Principal Unifié (Design Moderne Épuré Dribbble) */}
        <Route element={<LayoutModerne />}>
          {/* Espace Membre (Accessible à tous les membres connectés et actifs) */}
          <Route path="/tableau-de-bord" element={<TableauDeBord />} />
          <Route path="/prets" element={<ListePrets />} />
          <Route path="/mes-gains" element={<HistoriqueGains />} />
          <Route path="/cycles" element={<ListeCycles />} />
          <Route path="/profil" element={<EditerProfil />} />

          {/* Espace Bureau (Admin Président & Trésorier) */}
          <Route element={<RouteBureau />}>
            <Route path="/seances" element={<ListeSeances />} />
            <Route path="/seances/:id" element={<DetailSeance />} />
            <Route path="/sanctions" element={<ListeSanctions />} />
            <Route path="/utilisateurs" element={<GestionMembres />} />
          </Route>

          {/* Espace Super-Admin (Supervision SaaS Plateforme) */}
          <Route element={<RouteSuperAdmin />}>
            <Route path="/super-admin" element={<TableauDeBordSaaS />} />
            <Route path="/super-admin/tenants" element={<GestionTenants />} />
            <Route path="/super-admin/utilisateurs" element={<ListeUtilisateursGlobal />} />
          </Route>
        </Route>

        {/* Redirections de compatibilité pour les anciennes routes de transition "/moderne" */}
        <Route path="/moderne" element={<Navigate to="/tableau-de-bord" replace />} />
        <Route path="/moderne/seances" element={<Navigate to="/seances" replace />} />
        <Route path="/moderne/seances/:id" element={<Navigate to="/seances/:id" replace />} />
        <Route path="/moderne/prets" element={<Navigate to="/prets" replace />} />
        <Route path="/moderne/cycles" element={<Navigate to="/cycles" replace />} />
        <Route path="/moderne/mes-gains" element={<Navigate to="/mes-gains" replace />} />
        <Route path="/moderne/utilisateurs" element={<Navigate to="/utilisateurs" replace />} />
        <Route path="/moderne/sanctions" element={<Navigate to="/sanctions" replace />} />
        <Route path="/moderne/profil" element={<Navigate to="/profil" replace />} />
        <Route path="/nouveau-tableau-de-bord" element={<Navigate to="/tableau-de-bord" replace />} />
      </Route>

      {/* Redirection par défaut vers le tableau de bord */}
      <Route path="*" element={<Navigate to="/tableau-de-bord" replace />} />
    </Routes>
  );
};

export default App;
