import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { RouteProtegee } from './components/layout/RouteProtegee';
import { RouteBureau } from './components/layout/RouteBureau';
import { RouteSuperAdmin } from './components/layout/RouteSuperAdmin';
import { LayoutApplication } from './components/layout/LayoutApplication';
import { LayoutInvite } from './components/layout/LayoutInvite';

// Pages Publiques / Invités
import { Connexion } from './pages/auth/Connexion';
import { Inscription } from './pages/auth/Inscription';

// Page Compte Inactif (Sas d'attente)
import { CompteInactif } from './pages/auth/CompteInactif';

// Pages Application
import { TableauDeBord } from './pages/dashboard/TableauDeBord';
import { ListePrets } from './pages/prets/ListePrets';
import { HistoriqueGains } from './pages/gains/HistoriqueGains';
import { ListeCycles } from './pages/cycles/ListeCycles';
import { EditerProfil } from './pages/profil/EditerProfil';

// Pages Gestion Bureau
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
      {/* Routes Publiques (Invité) */}
      <Route element={<LayoutInvite />}>
        <Route path="/connexion" element={<Connexion />} />
        <Route path="/inscription" element={<Inscription />} />
      </Route>

      {/* Routes Protégées (Authentification requise) */}
      <Route element={<RouteProtegee />}>
        {/* Sas d'attente pour comptes inactifs */}
        <Route path="/compte-inactif" element={<CompteInactif />} />

        {/* Espace Connecté Principal */}
        <Route element={<LayoutApplication />}>
          {/* Espace Membre */}
          <Route path="/tableau-de-bord" element={<TableauDeBord />} />
          <Route path="/prets" element={<ListePrets />} />
          <Route path="/mes-gains" element={<HistoriqueGains />} />
          <Route path="/cycles" element={<ListeCycles />} />
          <Route path="/profil" element={<EditerProfil />} />

          {/* Espace Bureau (Admin & Trésorier) */}
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
      </Route>

      {/* Redirection par défaut */}
      <Route path="*" element={<Navigate to="/tableau-de-bord" replace />} />
    </Routes>
  );
};

export default App;
