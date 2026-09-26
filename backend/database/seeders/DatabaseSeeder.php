<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Cycle;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =========================================================================
        // 1. SUPER-ADMINISTRATEUR (Propriétaire / Manager Global de la Plateforme)
        // =========================================================================
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@tontine.test'],
            [
                'tenant_id' => null, // Non confiné à une tontine spécifique
                'name' => 'Super-Administrateur Plateforme',
                'phone' => '+237690000000',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'est_super_admin' => true,
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 2. ORGANISATION 1 : Amicale Solidarité Paris
        // =========================================================================
        $tenant1 = Tenant::firstOrCreate(
            ['slug' => 'amicale-solidarite-paris'],
            [
                'nom' => 'Amicale Solidarité Paris',
                'statut' => 'actif',
                'devise' => 'FCFA',
                'description' => 'Association de solidarité et d’entraide financière - Section Paris',
                'configuration' => [
                    'taux_interet_defaut' => 10,
                    'mange_mille_actif' => true,
                ]
            ]
        );

        // Membres de la Tontine 1
        $admin1 = User::firstOrCreate(
            ['email' => 'admin@tontine.test'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Président Administrateur',
                'phone' => '+237690000001',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'est_super_admin' => false,
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $tresorier1 = User::firstOrCreate(
            ['email' => 'tresorier@tontine.test'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Trésorier Général',
                'phone' => '+237690000002',
                'password' => Hash::make('password'),
                'role' => 'tresorier',
                'est_super_admin' => false,
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $membre1 = User::firstOrCreate(
            ['email' => 'membre@tontine.test'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Peter Membre',
                'phone' => '+237690000003',
                'password' => Hash::make('password'),
                'role' => 'membre',
                'est_super_admin' => false,
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Compte inactif en attente d'activation pour la démo du Sas d'attente
        User::firstOrCreate(
            ['email' => 'nouveau@tontine.test'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Nouveau Postulant',
                'phone' => '+237690000004',
                'password' => Hash::make('password'),
                'role' => 'membre',
                'est_super_admin' => false,
                'status' => 'actif',
                'is_active' => false, // Inactif
                'email_verified_at' => now(),
            ]
        );

        // Cycle 1 pour la Tontine 1
        $cycle1 = Cycle::withoutGlobalScope('tenant')->firstOrCreate(
            ['nom' => 'Cycle Inaugural 2026', 'tenant_id' => $tenant1->id],
            [
                'date_debut' => '2026-01-01',
                'date_fin' => '2026-12-31',
                'montant_part' => 10000,
                'montant_mange_mille' => 1000,
                'taux_interet' => 10,
                'taux_interet_banque' => 0,
                'est_actif' => true,
                'frequence_paiement' => 'mensuelle',
            ]
        );

        if (!$cycle1->membres()->where('user_id', $admin1->id)->exists()) {
            $cycle1->membres()->attach($admin1->id, ['rang' => 1]);
        }
        if (!$cycle1->membres()->where('user_id', $tresorier1->id)->exists()) {
            $cycle1->membres()->attach($tresorier1->id, ['rang' => 2]);
        }
        if (!$cycle1->membres()->where('user_id', $membre1->id)->exists()) {
            $cycle1->membres()->attach($membre1->id, ['rang' => 3]);
        }

        // =========================================================================
        // 3. ORGANISATION 2 : Cercle Épargne Diaspora (Tontine Indépendante)
        // =========================================================================
        $tenant2 = Tenant::firstOrCreate(
            ['slug' => 'cercle-epargne-diaspora'],
            [
                'nom' => 'Cercle Épargne Diaspora',
                'statut' => 'actif',
                'devise' => 'EUR',
                'description' => 'Cercle privé d’investissement rotatif et épargne collective',
                'configuration' => [
                    'taux_interet_defaut' => 5,
                    'mange_mille_actif' => false,
                ]
            ]
        );

        // Membres de la Tontine 2
        $admin2 = User::firstOrCreate(
            ['email' => 'admin2@tontine.test'],
            [
                'tenant_id' => $tenant2->id,
                'name' => 'Claire Présidente Tontine 2',
                'phone' => '+237690000021',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'est_super_admin' => false,
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $membre2 = User::firstOrCreate(
            ['email' => 'membre2@tontine.test'],
            [
                'tenant_id' => $tenant2->id,
                'name' => 'Paul Membre Tontine 2',
                'phone' => '+237690000022',
                'password' => Hash::make('password'),
                'role' => 'membre',
                'est_super_admin' => false,
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Cycle pour la Tontine 2
        $cycle2 = Cycle::withoutGlobalScope('tenant')->firstOrCreate(
            ['nom' => 'Cycle Diaspora 2026', 'tenant_id' => $tenant2->id],
            [
                'date_debut' => '2026-02-01',
                'date_fin' => '2026-11-30',
                'montant_part' => 25000,
                'montant_mange_mille' => 2500,
                'taux_interet' => 5,
                'taux_interet_banque' => 0,
                'est_actif' => true,
                'frequence_paiement' => 'mensuelle',
            ]
        );

        if (!$cycle2->membres()->where('user_id', $admin2->id)->exists()) {
            $cycle2->membres()->attach($admin2->id, ['rang' => 1]);
        }
        if (!$cycle2->membres()->where('user_id', $membre2->id)->exists()) {
            $cycle2->membres()->attach($membre2->id, ['rang' => 2]);
        }
    }
}
