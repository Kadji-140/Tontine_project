import React, { useEffect, useState } from 'react';
import { CarteKPI } from '../../components/dashboard/CarteKPI';
import { CarteProchaineSeance } from '../../components/dashboard/CarteProchaineSeance';
import { GraphiqueTresorerie } from '../../components/dashboard/GraphiqueTresorerie';
import { TableActivitesRecentes } from '../../components/dashboard/TableActivitesRecentes';
import api from '../../lib/api';
import { Cycle, Seance, Cotisation } from '../../types';
import {
  Wallet,
  ArrowUpRight,
  PiggyBank,
  AlertTriangle,
  Coins,
  ShieldCheck,
  Loader2,
  RefreshCw
} from 'lucide-react';
import { Bouton } from '../../components/ui/Bouton';

export const TableauDeBord: React.FC = () => {
  const [chargement, setChargement] = useState(true);
  const [donnees, setDonnees] = useState<{
    est_bureau: boolean;
    cycle_actif?: Cycle | null;
    prochaine_seance?: Seance | null;
    solde_caisse?: number;
    argent_dehors?: number;
    total_cotisations?: number;
    total_remboursements?: number;
    total_depenses?: number;
    total_tontine?: number;
    total_secours?: number;
    activites_recentes?: Cotisation[];
    // Membre
    mon_epargne?: number;
    mes_prets?: number;
    restant_du?: number;
    mes_dettes?: number;
    mes_sanctions?: number;
  } | null>(null);

  const chargerDashboard = async () => {
    setChargement(true);
    try {
      const reponse = await api.get('/dashboard');
      if (reponse.data.succes) {
        setDonnees(reponse.data);
      }
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerDashboard();
  }, []);

  if (chargement) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] space-y-3">
        <Loader2 className="w-8 h-8 animate-spin text-primaire-600" />
        <p className="text-xs font-medium text-slate-500">Chargement de vos indicateurs financiers...</p>
      </div>
    );
  }

  const estBureau = donnees?.est_bureau ?? false;

  return (
    <div className="space-y-6">
      {/* En-tête avec titre et bouton actualiser */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            Tableau de Bord {estBureau ? '(Gestion du Bureau)' : '(Espace Membre)'}
          </h2>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            {donnees?.cycle_actif
              ? `Cycle actif : ${donnees.cycle_actif.nom}`
              : 'Aucun cycle actuellement actif'}
          </p>
        </div>

        <Bouton
          variante="contour"
          taille="sm"
          onClick={chargerDashboard}
          icone={<RefreshCw className="w-3.5 h-3.5" />}
        >
          Actualiser
        </Bouton>
      </div>

      {/* Cartes KPI selon le rôle */}
      {estBureau ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <CarteKPI
            titre="Solde Caisse Global"
            valeur={donnees?.solde_caisse ?? 0}
            couleur="emeraude"
            icone={<Wallet className="w-5 h-5" />}
            description="Entrées nettes - Sorties"
          />

          <CarteKPI
            titre="Argent Dehors (Crédits)"
            valeur={donnees?.argent_dehors ?? 0}
            couleur="ambre"
            icone={<ArrowUpRight className="w-5 h-5" />}
            description="Créances en cours de remboursement"
          />

          <CarteKPI
            titre="Total Cotisations"
            valeur={donnees?.total_cotisations ?? 0}
            couleur="indigo"
            icone={<Coins className="w-5 h-5" />}
            description="Épargnes & tontine cumulées"
          />

          <CarteKPI
            titre="Total Dépenses Validées"
            valeur={donnees?.total_depenses ?? 0}
            couleur="rose"
            icone={<AlertTriangle className="w-5 h-5" />}
            description="Frais et charges du cycle"
          />
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <CarteKPI
            titre="Mon Épargne (Tontine)"
            valeur={donnees?.mon_epargne ?? 0}
            couleur="emeraude"
            icone={<PiggyBank className="w-5 h-5" />}
            description="Mes cotisations sur le cycle"
          />

          <CarteKPI
            titre="Mes Prêts Actifs"
            valeur={donnees?.mes_prets ?? 0}
            couleur="indigo"
            icone={<Wallet className="w-5 h-5" />}
            description="Montant emprunté en cours"
          />

          <CarteKPI
            titre="Reste à Rembourser"
            valeur={donnees?.restant_du ?? 0}
            couleur="rose"
            icone={<ArrowUpRight className="w-5 h-5" />}
            description="Capital restant dû + intérêts"
          />

          <CarteKPI
            titre="Mes Sanctions Impayées"
            valeur={donnees?.mes_sanctions ?? 0}
            couleur="ambre"
            icone={<AlertTriangle className="w-5 h-5" />}
            description="Amendes en attente de règlement"
          />
        </div>
      )}

      {/* Rangée : Prochaine séance & Activités récentes */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-1">
          <CarteProchaineSeance
            seance={donnees?.prochaine_seance}
            cycleActif={donnees?.cycle_actif}
          />
        </div>

        <div className="lg:col-span-2">
          <TableActivitesRecentes activites={donnees?.activites_recentes} />
        </div>
      </div>

      {/* Graphique de Trésorerie */}
      <div>
        <GraphiqueTresorerie />
      </div>
    </div>
  );
};
