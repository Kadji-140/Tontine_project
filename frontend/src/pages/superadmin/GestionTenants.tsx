import React, { useEffect, useState } from 'react';
import api from '../../lib/api';
import { Tenant } from '../../types';
import { Carte } from '../../components/ui/Carte';
import { Badge } from '../../components/ui/Badge';
import { Bouton } from '../../components/ui/Bouton';
import { ChampTexte } from '../../components/ui/ChampTexte';
import { Modal } from '../../components/ui/Modal';
import { 
  Building2, 
  Search, 
  PlusCircle, 
  Power, 
  AlertTriangle, 
  CheckCircle2, 
  Users, 
  Wallet,
  Calendar
} from 'lucide-react';

export const GestionTenants: React.FC = () => {
  const [tenants, setTenants] = useState<Tenant[]>([]);
  const [chargement, setChargement] = useState(true);
  const [recherche, setRecherche] = useState('');
  const [filtreStatut, setFiltreStatut] = useState<'tous' | 'actif' | 'suspendu'>('tous');

  // Modale de création
  const [modaleCreationOuverte, setModaleCreationOuverte] = useState(false);
  const [chargementCreation, setChargementCreation] = useState(false);
  const [erreurCreation, setErreurCreation] = useState<string | null>(null);

  const [formulaire, setFormulaire] = useState({
    nom: '',
    slug: '',
    devise: 'FCFA',
    description: '',
    admin_nom: '',
    admin_email: '',
    admin_phone: '',
    admin_password: '',
  });

  // Action de suspension
  const [tenantEnModification, setTenantEnModification] = useState<Tenant | null>(null);
  const [actionStatutEnCours, setActionStatutEnCours] = useState(false);

  const chargerTenants = async () => {
    setChargement(true);
    try {
      const params: Record<string, string> = {};
      if (recherche) params.recherche = recherche;
      if (filtreStatut !== 'tous') params.statut = filtreStatut;

      const reponse = await api.get<{ succes: boolean; tenants: { data: Tenant[] } }>('/super-admin/tenants', { params });
      if (reponse.data.succes) {
        setTenants(reponse.data.tenants.data);
      }
    } catch {
      // Ignorer
    } finally {
      setChargement(false);
    }
  };

  useEffect(() => {
    chargerTenants();
  }, [recherche, filtreStatut]);

  const genererSlug = (nom: string) => {
    return nom
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  };

  const handleNomChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value;
    setFormulaire(prev => ({
      ...prev,
      nom: val,
      slug: genererSlug(val),
    }));
  };

  const soumettreCreation = async (e: React.FormEvent) => {
    e.preventDefault();
    setChargementCreation(true);
    setErreurCreation(null);

    try {
      await api.post('/super-admin/tenants', formulaire);
      setModaleCreationOuverte(false);
      setFormulaire({
        nom: '',
        slug: '',
        devise: 'FCFA',
        description: '',
        admin_nom: '',
        admin_email: '',
        admin_phone: '',
        admin_password: '',
      });
      chargerTenants();
    } catch (err: any) {
      setErreurCreation(err.response?.data?.message || 'Erreur lors de la création de la tontine.');
    } finally {
      setChargementCreation(false);
    }
  };

  const basculerStatut = async (tenant: Tenant) => {
    setActionStatutEnCours(true);
    try {
      await api.patch(`/super-admin/tenants/${tenant.id}/statut`);
      setTenantEnModification(null);
      chargerTenants();
    } catch {
      // Ignorer
    } finally {
      setActionStatutEnCours(false);
    }
  };

  const formaterDevise = (montant?: number) => {
    if (!montant) return '0';
    return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(montant);
  };

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
            <Building2 className="w-7 h-7 text-amber-600" />
            Gestion des Organisations Tontines
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Supervisez les organisations clientes, activez ou suspendez leurs accès en temps réel.
          </p>
        </div>

        <Bouton
          variante="primaire"
          onClick={() => setModaleCreationOuverte(true)}
          className="bg-amber-600 hover:bg-amber-700 text-white flex items-center gap-2"
        >
          <PlusCircle className="w-4 h-4" />
          Nouvelle Organisation
        </Bouton>
      </div>

      {/* Barre de recherche et filtres */}
      <Carte className="p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="relative w-full sm:w-80">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            placeholder="Rechercher par nom ou slug..."
            value={recherche}
            onChange={(e) => setRecherche(e.target.value)}
            className="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-sombre-bordure bg-white dark:bg-sombre-surface text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500"
          />
        </div>

        <div className="flex items-center gap-2 self-start sm:self-auto">
          {(['tous', 'actif', 'suspendu'] as const).map((st) => (
            <button
              key={st}
              onClick={() => setFiltreStatut(st)}
              className={`px-3 py-1.5 text-xs font-bold rounded-lg transition-colors capitalize ${
                filtreStatut === st
                  ? 'bg-amber-600 text-white'
                  : 'bg-slate-100 dark:bg-sombre-survol text-slate-600 dark:text-slate-400 hover:bg-slate-200'
              }`}
            >
              {st}
            </button>
          ))}
        </div>
      </Carte>

      {/* Tableau des Tontines */}
      <Carte className="overflow-hidden p-0">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead className="bg-slate-50 dark:bg-sombre-survol text-xs uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-sombre-bordure">
              <tr>
                <th className="py-3.5 px-4">Organisation</th>
                <th className="py-3.5 px-4">Administrateur</th>
                <th className="py-3.5 px-4">Cycle Actif</th>
                <th className="py-3.5 px-4">Membres / Trésorerie</th>
                <th className="py-3.5 px-4">Statut</th>
                <th className="py-3.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 dark:divide-sombre-bordure">
              {chargement ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400">
                    Chargement des organisations...
                  </td>
                </tr>
              ) : tenants.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-12 text-center text-slate-400">
                    Aucune organisation trouvée.
                  </td>
                </tr>
              ) : (
                tenants.map((t) => (
                  <tr key={t.id} className="hover:bg-slate-50/60 dark:hover:bg-sombre-survol/50 transition-colors">
                    <td className="py-4 px-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-400 font-bold flex items-center justify-center text-sm">
                          {t.nom.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                          <p className="font-bold text-slate-900 dark:text-white">{t.nom}</p>
                          <p className="text-xs font-mono text-amber-600">{t.slug}</p>
                        </div>
                      </div>
                    </td>

                    <td className="py-4 px-4">
                      {t.admin_principal ? (
                        <div>
                          <p className="font-medium text-slate-900 dark:text-white">{t.admin_principal.name}</p>
                          <p className="text-xs text-slate-400">{t.admin_principal.email}</p>
                        </div>
                      ) : (
                        <span className="text-xs text-slate-400 italic">Aucun admin</span>
                      )}
                    </td>

                    <td className="py-4 px-4">
                      {t.cycle_actif ? (
                        <div className="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300">
                          <Calendar className="w-3.5 h-3.5 text-primaire-500" />
                          <span>{t.cycle_actif.nom}</span>
                        </div>
                      ) : (
                        <span className="text-xs text-slate-400 italic">Aucun cycle actif</span>
                      )}
                    </td>

                    <td className="py-4 px-4">
                      <div className="space-y-0.5 text-xs">
                        <div className="flex items-center gap-1 text-slate-700 dark:text-slate-300">
                          <Users className="w-3.5 h-3.5 text-slate-400" />
                          <span>{t.utilisateurs_count ?? 0} membres</span>
                        </div>
                        <div className="flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400">
                          <Wallet className="w-3.5 h-3.5" />
                          <span>{formaterDevise(t.total_epargne)} {t.devise}</span>
                        </div>
                      </div>
                    </td>

                    <td className="py-4 px-4">
                      <Badge variante={t.statut === 'actif' ? 'succes' : 'danger'}>
                        {t.statut}
                      </Badge>
                    </td>

                    <td className="py-4 px-4 text-right">
                      <Bouton
                        variante={t.statut === 'actif' ? 'danger' : 'primaire'}
                        taille="sm"
                        onClick={() => setTenantEnModification(t)}
                        className="gap-1.5"
                      >
                        <Power className="w-3.5 h-3.5" />
                        <span>{t.statut === 'actif' ? 'Suspendre' : 'Activer'}</span>
                      </Bouton>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </Carte>

      {/* Modale de Confirmation Activation / Suspension */}
      <Modal
        estOuvert={!!tenantEnModification}
        surFermer={() => setTenantEnModification(null)}
        titre={tenantEnModification?.statut === 'actif' ? "Suspendre l'Organisation" : "Réactiver l'Organisation"}
      >
        <div className="space-y-4">
          <div className="flex items-center gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50">
            <AlertTriangle className="w-6 h-6 flex-shrink-0" />
            <p className="text-xs">
              {tenantEnModification?.statut === 'actif'
                ? `La suspension bloquera immédiatement l'accès à l'ensemble des membres et administrateurs de "${tenantEnModification?.nom}".`
                : `La réactivation rétablira immédiatement les accès pour les utilisateurs de "${tenantEnModification?.nom}".`}
            </p>
          </div>

          <div className="flex justify-end gap-3 pt-2">
            <Bouton variante="secondaire" onClick={() => setTenantEnModification(null)}>
              Annuler
            </Bouton>
            <Bouton
              variante={tenantEnModification?.statut === 'actif' ? 'danger' : 'primaire'}
              onClick={() => tenantEnModification && basculerStatut(tenantEnModification)}
              chargement={actionStatutEnCours}
            >
              {tenantEnModification?.statut === 'actif' ? 'Confirmer la Suspension' : 'Confirmer la Réactivation'}
            </Bouton>
          </div>
        </div>
      </Modal>

      {/* Modale de Création d'Organisation */}
      <Modal
        estOuvert={modaleCreationOuverte}
        surFermer={() => setModaleCreationOuverte(false)}
        titre="Créer une Nouvelle Organisation Tontine"
      >
        <form onSubmit={soumettreCreation} className="space-y-4">
          {erreurCreation && (
            <div className="p-3 text-xs rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200">
              {erreurCreation}
            </div>
          )}

          <div className="space-y-3">
            <p className="text-xs font-bold uppercase tracking-wider text-amber-600">
              1. Informations de l'Organisation
            </p>
            <ChampTexte
              etiquette="Nom de la Tontine / Association"
              value={formulaire.nom}
              onChange={handleNomChange}
              placeholder="Ex: Mutuelle des Cadres de Douala"
              required
            />
            <div className="grid grid-cols-2 gap-3">
              <ChampTexte
                etiquette="Identifiant unique (Slug)"
                value={formulaire.slug}
                onChange={(e) => setFormulaire({ ...formulaire, slug: e.target.value })}
                placeholder="mutuelle-cadres-douala"
                required
              />
              <ChampTexte
                etiquette="Devise"
                value={formulaire.devise}
                onChange={(e) => setFormulaire({ ...formulaire, devise: e.target.value })}
                placeholder="FCFA, EUR, USD"
                required
              />
            </div>
          </div>

          <div className="space-y-3 pt-2 border-t border-slate-100 dark:border-sombre-bordure">
            <p className="text-xs font-bold uppercase tracking-wider text-primaire-600">
              2. Premier Administrateur Référent
            </p>
            <ChampTexte
              etiquette="Nom Complet"
              value={formulaire.admin_nom}
              onChange={(e) => setFormulaire({ ...formulaire, admin_nom: e.target.value })}
              placeholder="Ex: Jean Dupont"
              required
            />
            <div className="grid grid-cols-2 gap-3">
              <ChampTexte
                etiquette="Email"
                type="email"
                value={formulaire.admin_email}
                onChange={(e) => setFormulaire({ ...formulaire, admin_email: e.target.value })}
                placeholder="president@organisation.org"
                required
              />
              <ChampTexte
                etiquette="Téléphone"
                value={formulaire.admin_phone}
                onChange={(e) => setFormulaire({ ...formulaire, admin_phone: e.target.value })}
                placeholder="+237690000000"
                required
              />
            </div>
            <ChampTexte
              etiquette="Mot de passe initial"
              type="password"
              value={formulaire.admin_password}
              onChange={(e) => setFormulaire({ ...formulaire, admin_password: e.target.value })}
              placeholder="Minimum 8 caractères"
              required
            />
          </div>

          <div className="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-sombre-bordure">
            <Bouton type="button" variante="secondaire" onClick={() => setModaleCreationOuverte(false)}>
              Annuler
            </Bouton>
            <Bouton type="submit" variante="primaire" chargement={chargementCreation} className="bg-amber-600 hover:bg-amber-700 text-white">
              Créer l'Organisation
            </Bouton>
          </div>
        </form>
      </Modal>
    </div>
  );
};

export default GestionTenants;
