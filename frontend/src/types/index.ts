export type RoleUtilisateur = 'admin' | 'tresorier' | 'membre';

export interface Tenant {
  id: number;
  nom: string;
  slug: string;
  statut: 'actif' | 'suspendu';
  devise: string;
  description?: string | null;
  configuration?: Record<string, any>;
  utilisateurs_count?: number;
  cycles_count?: number;
  cycle_actif?: Cycle | null;
  admin_principal?: Utilisateur | null;
  total_epargne?: number;
  created_at: string;
  updated_at: string;
}

export interface Utilisateur {
  id: number;
  tenant_id?: number | null;
  tenant?: Tenant | null;
  name: string;
  email: string;
  phone: string;
  role: RoleUtilisateur;
  est_super_admin?: boolean;
  status: 'actif' | 'suspendu';
  is_active: boolean;
  avatar?: string | null;
  profession?: string | null;
  adresse?: string | null;
  cni?: string | null;
  beneficiaire?: string | null;
  created_at: string;
  updated_at: string;
}

export interface Cycle {
  id: number;
  nom: string;
  date_debut: string;
  date_fin: string;
  montant_part: number;
  montant_mange_mille: number;
  taux_interet: number;
  frequence_paiement: 'mensuelle' | 'hebdomadaire' | 'bimensuelle';
  est_actif: boolean;
  membres_count?: number;
  seances_count?: number;
  membres?: (Utilisateur & { pivot: { rang: number } })[];
  seances?: Seance[];
}

export interface Seance {
  id: number;
  cycle_id: number;
  date_seance: string;
  statut: 'ouverte' | 'fermee';
  total_encaisse: number;
  preuve_versement?: string | null;
  etat_versement: 'non_verse' | 'en_attente' | 'valide' | 'rejete';
  cycle?: Cycle;
  cotisations?: Cotisation[];
  prets?: Pret[];
  remboursements?: Remboursement[];
  sanctions?: Sanction[];
  depenses?: Depense[];
}

export interface Cotisation {
  id: number;
  seance_id: number;
  user_id: number;
  montant: number;
  type: 'tontine' | 'secours' | 'autre';
  enregistre_par?: number;
  created_at: string;
  user?: Utilisateur;
  seance?: Seance;
}

export interface Pret {
  id: number;
  user_id: number;
  seance_id: number;
  montant_demande: number;
  interet_total: number;
  total_a_rembourser: number;
  date_echeance: string;
  date_echeance_modifiee: boolean;
  date_modification_proposee?: string | null;
  est_accepte_par_membre: boolean;
  statut: 'en_attente' | 'valide' | 'rembourse' | 'rejete';
  created_at: string;
  user?: Utilisateur;
  seance?: Seance;
  remboursements?: Remboursement[];
}

export interface Remboursement {
  id: number;
  pret_id: number;
  seance_id: number;
  user_id: number;
  montant: number;
  enregistre_par?: number;
  created_at: string;
  user?: Utilisateur;
  pret?: Pret;
}

export interface Sanction {
  id: number;
  user_id: number;
  seance_id?: number | null;
  montant: number;
  motif: string;
  est_reglee: boolean;
  created_at: string;
  user?: Utilisateur;
  seance?: Seance;
}

export interface Depense {
  id: number;
  seance_id: number;
  montant: number;
  motif: string;
  statut: 'en_attente' | 'validee' | 'rejetee';
  enregistre_par?: number;
  created_at: string;
}

export interface Annonce {
  id: number;
  titre: string;
  message: string;
  auteur: string;
  created_at: string;
  est_lue: boolean;
}

export interface PaiementLot {
  id: number;
  cycle_id: number;
  user_id: number;
  seance_id: number;
  montant: number;
  date_paiement: string;
  statut: 'en_attente' | 'confirme' | 'rejete';
  user?: Utilisateur;
  cycle?: Cycle;
  seance?: Seance;
}
