<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Cycle;
use App\Models\Seance;
use App\Models\Cotisation;
use App\Models\Pret;
use App\Models\Remboursement;
use App\Models\Sanction;
use App\Models\Depense;
use App\Models\FondsDepense;
use App\Models\PaiementLot;
use App\Models\Annonce;

class TontineDataSeeder extends Seeder
{
    /**
     * Alimente l'ensemble des scénarios métier réels (cotisations, prêts, remboursements, sanctions, etc.)
     */
    public function run(): void
    {
        // =========================================================================
        // SCÉNARIOS COMPLETS POUR LA TONTINE 1 (Solidarité Yaoundé)
        // =========================================================================
        $t1 = Tenant::where('slug', 'solidarite-yaounde')->firstOrFail();

        // Récupérer les membres de T1
        $admin1 = User::where('email', 'admin@tontine.test')->firstOrFail();
        $tresorier1 = User::where('email', 'tresorier@tontine.test')->firstOrFail();
        $samuel = User::where('email', 'membre@tontine.test')->firstOrFail();
        $brigitte = User::where('email', 'brigitte@tontine.test')->firstOrFail();
        $patrick = User::where('email', 'patrick@tontine.test')->firstOrFail();
        $marie = User::where('email', 'marie@tontine.test')->firstOrFail();
        $alain = User::where('email', 'alain@tontine.test')->firstOrFail();

        $participantsT1 = [$admin1, $tresorier1, $samuel, $brigitte, $patrick, $marie, $alain];

        // 1. CYCLE DE TONTINE
        $cycle1 = Cycle::withoutGlobalScope('tenant')->firstOrCreate(
            ['nom' => 'Grand Cycle Solidarité 2026', 'tenant_id' => $t1->id],
            [
                'date_debut' => '2026-01-01',
                'date_fin' => '2026-07-31',
                'montant_part' => 50000,
                'montant_mange_mille' => 2500,
                'taux_interet' => 10,
                'taux_interet_banque' => 0,
                'est_actif' => true,
                'frequence_paiement' => 'mensuelle',
            ]
        );

        // Attachement des rangs de tirage de 1 à 7
        foreach ($participantsT1 as $index => $part) {
            if (!$cycle1->membres()->where('user_id', $part->id)->exists()) {
                $cycle1->membres()->attach($part->id, ['rang' => $index + 1]);
            }
        }

        // 2. SÉANCES MENSUELLES
        // Séance 1 (Janvier 2026) : Fermée
        $seance1 = Seance::withoutGlobalScope('tenant')->firstOrCreate(
            ['cycle_id' => $cycle1->id, 'date_seance' => '2026-01-15'],
            [
                'tenant_id' => $t1->id,
                'statut' => 'fermee',
                'total_encaisse' => 385000,
                'preuve_versement' => 'preuves/bordereau_afriland_janvier2026.pdf',
                'etat_versement' => 'valide',
            ]
        );

        // Séance 2 (Février 2026) : Fermée
        $seance2 = Seance::withoutGlobalScope('tenant')->firstOrCreate(
            ['cycle_id' => $cycle1->id, 'date_seance' => '2026-02-15'],
            [
                'tenant_id' => $t1->id,
                'statut' => 'fermee',
                'total_encaisse' => 385000,
                'preuve_versement' => 'preuves/bordereau_afriland_fevrier2026.pdf',
                'etat_versement' => 'valide',
            ]
        );

        // Séance 3 (Mars 2026) : Ouverte (séance en cours)
        $seance3 = Seance::withoutGlobalScope('tenant')->firstOrCreate(
            ['cycle_id' => $cycle1->id, 'date_seance' => '2026-03-15'],
            [
                'tenant_id' => $t1->id,
                'statut' => 'ouverte',
                'total_encaisse' => 0,
                'etat_versement' => 'non_verse',
            ]
        );

        // 3. COTISATIONS POUR SÉANCES 1 & 2
        foreach ([$seance1, $seance2] as $seance) {
            foreach ($participantsT1 as $part) {
                // Cotisation Tontine (50 000 FCFA)
                Cotisation::withoutGlobalScope('tenant')->firstOrCreate(
                    [
                        'tenant_id' => $t1->id,
                        'seance_id' => $seance->id,
                        'user_id' => $part->id,
                        'type' => 'tontine',
                    ],
                    [
                        'montant' => 50000,
                        'enregistre_par' => $tresorier1->id,
                    ]
                );

                // Cotisation Caisse de Secours (5 000 FCFA)
                Cotisation::withoutGlobalScope('tenant')->firstOrCreate(
                    [
                        'tenant_id' => $t1->id,
                        'seance_id' => $seance->id,
                        'user_id' => $part->id,
                        'type' => 'secours',
                    ],
                    [
                        'montant' => 5000,
                        'enregistre_par' => $tresorier1->id,
                    ]
                );
            }
        }

        // 4. PAIEMENT DES LOTS (CAGNOTTE REÇUE PAR LES BÉNÉFICIAIRES)
        // Rang 1 (Jean-Paul Kamga) a reçu la cagnotte en Séance 1
        PaiementLot::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'cycle_id' => $cycle1->id,
                'user_id' => $admin1->id,
                'seance_id' => $seance1->id,
            ],
            [
                'montant' => 350000,
                'date_paiement' => '2026-01-16',
                'statut' => 'confirme',
            ]
        );

        // Rang 2 (Chantal Ngono) a reçu la cagnotte en Séance 2
        PaiementLot::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'cycle_id' => $cycle1->id,
                'user_id' => $tresorier1->id,
                'seance_id' => $seance2->id,
            ],
            [
                'montant' => 350000,
                'date_paiement' => '2026-02-16',
                'statut' => 'confirme',
            ]
        );

        // Rang 3 (Samuel Eto'o Mbarga) est le bénéficiaire attendu pour la Séance 3
        PaiementLot::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'cycle_id' => $cycle1->id,
                'user_id' => $samuel->id,
                'seance_id' => $seance3->id,
            ],
            [
                'montant' => 350000,
                'date_paiement' => null,
                'statut' => 'en_attente',
            ]
        );

        // 5. PRÊTS ET REMBOURSEMENTS
        // Prêt 1 : Samuel Eto'o - 150 000 FCFA (remboursé intégralement)
        $pret1 = Pret::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'user_id' => $samuel->id,
                'seance_id' => $seance1->id,
                'montant_demande' => 150000,
            ],
            [
                'interet_total' => 15000,
                'date_echeance' => '2026-06-30',
                'statut' => 'rembourse',
                'est_accepte_par_membre' => true,
            ]
        );

        Remboursement::withoutGlobalScope('tenant')->firstOrCreate(
            ['pret_id' => $pret1->id],
            [
                'tenant_id' => $t1->id,
                'seance_id' => $seance2->id,
                'user_id' => $samuel->id,
                'enregistre_par' => $tresorier1->id,
                'montant' => 165000, // Capital 150k + Intérêts 15k
            ]
        );

        // Prêt 2 : Brigitte Mengue - 200 000 FCFA (en cours, partiellement remboursé)
        $pret2 = Pret::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'user_id' => $brigitte->id,
                'seance_id' => $seance2->id,
                'montant_demande' => 200000,
            ],
            [
                'interet_total' => 20000,
                'date_echeance' => '2026-08-31',
                'statut' => 'valide',
                'est_accepte_par_membre' => true,
            ]
        );

        // Remboursement partiel de 110 000 FCFA
        Remboursement::withoutGlobalScope('tenant')->firstOrCreate(
            ['pret_id' => $pret2->id, 'montant' => 110000],
            [
                'tenant_id' => $t1->id,
                'seance_id' => $seance2->id,
                'user_id' => $brigitte->id,
                'enregistre_par' => $tresorier1->id,
            ]
        );

        // Prêt 3 : Alain Kenmogne - 100 000 FCFA (demande soumise, en attente)
        Pret::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'user_id' => $alain->id,
                'seance_id' => $seance3->id,
                'montant_demande' => 100000,
            ],
            [
                'interet_total' => 10000,
                'date_echeance' => '2026-09-30',
                'statut' => 'en_attente',
                'est_accepte_par_membre' => true,
            ]
        );

        // 6. SANCTIONS & AMENDES
        // Sanction réglée : Retard de Patrick Fotso
        Sanction::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'user_id' => $patrick->id,
                'seance_id' => $seance1->id,
                'motif' => 'Retard de 45 minutes au démarrage de la séance',
            ],
            [
                'montant' => 5000,
                'est_reglee' => true,
            ]
        );

        // Sanction non réglée : Absence injustifiée Alain Kenmogne
        Sanction::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'user_id' => $alain->id,
                'seance_id' => $seance2->id,
                'motif' => 'Absence non justifiée lors de la séance mensuelle',
            ],
            [
                'montant' => 10000,
                'est_reglee' => false,
            ]
        );

        // Sanction non réglée : Sonnerie téléphone Marie-Claire
        Sanction::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'user_id' => $marie->id,
                'seance_id' => $seance2->id,
                'motif' => 'Perturbation de séance par sonnerie téléphonique',
            ],
            [
                'montant' => 2500,
                'est_reglee' => false,
            ]
        );

        // 7. DÉPENSES
        Depense::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'seance_id' => $seance1->id,
                'motif' => 'Location de la salle climatisée et sonorisation',
            ],
            [
                'enregistre_par' => $tresorier1->id,
                'montant' => 30000,
                'statut' => 'validee',
                'justificatif' => 'factures/salle_janvier.pdf',
            ]
        );

        Depense::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'seance_id' => $seance2->id,
                'motif' => 'Service traiteur, collations et rafraîchissements',
            ],
            [
                'enregistre_par' => $tresorier1->id,
                'montant' => 20000,
                'statut' => 'validee',
                'justificatif' => 'factures/traiteur_fevrier.pdf',
            ]
        );

        Depense::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'seance_id' => $seance3->id,
                'motif' => 'Soutien de solidarité pour heureux événement d\'un membre',
            ],
            [
                'enregistre_par' => $admin1->id,
                'montant' => 50000,
                'statut' => 'en_attente',
            ]
        );

        // 8. FONDS DE DÉPENSES (CAISSES ANNEXES)
        FondsDepense::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'cycle_id' => $cycle1->id,
                'type' => 'mange_mille',
            ],
            [
                'seance_id' => $seance1->id,
                'montant' => 17500,
                'description' => 'Prélèvement solidaire mange-mille sur 7 membres',
            ]
        );

        FondsDepense::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'cycle_id' => $cycle1->id,
                'type' => 'secours',
            ],
            [
                'seance_id' => $seance1->id,
                'montant' => 35000,
                'description' => 'Alimentation de la caisse d’urgence et de secours',
            ]
        );

        // 9. ANNONCES DE LA TONTINE
        Annonce::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'titre' => 'Lancement officiel de l\'exercice 2026 - Mot du Président',
            ],
            [
                'message' => 'Chers membres, nous entamons ce nouvel exercice sous le signe de la rigueur, de la fraternité et de la transparence financière. Merci de respecter scrupuleusement les horaires.',
                'user_id' => $admin1->id,
                'target_role' => null,
            ]
        );

        Annonce::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t1->id,
                'titre' => 'Rappel : Clôture des demandes de prêts pour la Séance 3',
            ],
            [
                'message' => 'Les membres souhaitant bénéficier d\'un prêt solidaire pour la prochaine séance sont priés de soumettre leur dossier au Trésorier au moins 48 heures à l\'avance.',
                'user_id' => $tresorier1->id,
                'target_role' => null,
            ]
        );

        // =========================================================================
        // SCÉNARIOS COMPLETS POUR LA TONTINE 2 (Investissement Douala)
        // =========================================================================
        $t2 = Tenant::where('slug', 'investissement-douala')->firstOrFail();

        $admin2 = User::where('email', 'admin2@tontine.test')->firstOrFail();
        $tresorier2 = User::where('email', 'tresorier2@tontine.test')->firstOrFail();
        $eric = User::where('email', 'eric@tontine.test')->firstOrFail();
        $danielle = User::where('email', 'danielle@tontine.test')->firstOrFail();
        $fabrice = User::where('email', 'fabrice@tontine.test')->firstOrFail();

        $participantsT2 = [$admin2, $tresorier2, $eric, $danielle, $fabrice];

        // 1. CYCLE DE TONTINE (100 000 FCFA / part)
        $cycle2 = Cycle::withoutGlobalScope('tenant')->firstOrCreate(
            ['nom' => 'Cercle Business Akwa 2026', 'tenant_id' => $t2->id],
            [
                'date_debut' => '2026-02-01',
                'date_fin' => '2026-06-30',
                'montant_part' => 100000,
                'montant_mange_mille' => 5000,
                'taux_interet' => 5,
                'taux_interet_banque' => 0,
                'est_actif' => true,
                'frequence_paiement' => 'mensuelle',
            ]
        );

        foreach ($participantsT2 as $idx => $part) {
            if (!$cycle2->membres()->where('user_id', $part->id)->exists()) {
                $cycle2->membres()->attach($part->id, ['rang' => $idx + 1]);
            }
        }

        // 2. SÉANCE 1 TONTINE 2
        $seanceT2 = Seance::withoutGlobalScope('tenant')->firstOrCreate(
            ['cycle_id' => $cycle2->id, 'date_seance' => '2026-02-20'],
            [
                'tenant_id' => $t2->id,
                'statut' => 'fermee',
                'total_encaisse' => 500000,
                'preuve_versement' => 'preuves/versement_ecobank_douala.pdf',
                'etat_versement' => 'valide',
            ]
        );

        foreach ($participantsT2 as $part) {
            Cotisation::withoutGlobalScope('tenant')->firstOrCreate(
                [
                    'tenant_id' => $t2->id,
                    'seance_id' => $seanceT2->id,
                    'user_id' => $part->id,
                    'type' => 'tontine',
                ],
                [
                    'montant' => 100000,
                    'enregistre_par' => $tresorier2->id,
                ]
            );
        }

        // Prêt dans Tontine 2 : Eric Ndongo emprunte 300 000 FCFA
        Pret::withoutGlobalScope('tenant')->firstOrCreate(
            [
                'tenant_id' => $t2->id,
                'user_id' => $eric->id,
                'seance_id' => $seanceT2->id,
                'montant_demande' => 300000,
            ],
            [
                'interet_total' => 15000,
                'date_echeance' => '2026-07-31',
                'statut' => 'valide',
                'est_accepte_par_membre' => true,
            ]
        );
    }
}
