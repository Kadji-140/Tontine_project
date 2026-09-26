<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
        // 1. Compte Administrateur / Président
        $admin = User::firstOrCreate(
            ['email' => 'admin@tontine.test'],
            [
                'name' => 'Président Administrateur',
                'phone' => '+237690000001',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Compte Trésorier
        $tresorier = User::firstOrCreate(
            ['email' => 'tresorier@tontine.test'],
            [
                'name' => 'Trésorier Général',
                'phone' => '+237690000002',
                'password' => Hash::make('password'),
                'role' => 'tresorier',
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Compte Membre ordinaire
        $membre = User::firstOrCreate(
            ['email' => 'membre@tontine.test'],
            [
                'name' => 'Peter Membre',
                'phone' => '+237690000003',
                'password' => Hash::make('password'),
                'role' => 'membre',
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 4. Cycle Actif initial
        $cycle = Cycle::firstOrCreate(
            ['nom' => 'Cycle Inaugural 2026'],
            [
                'date_debut' => '2026-01-01',
                'date_fin' => '2026-12-31',
                'montant_part' => 10000,
                'montant_mange_mille' => 1000,
                'taux_interet' => 10,
                'est_actif' => true,
                'frequence_paiement' => 'mensuelle',
            ]
        );

        // 5. Rattachement des membres au cycle avec leur rang
        if (!$cycle->membres()->where('user_id', $admin->id)->exists()) {
            $cycle->membres()->attach($admin->id, ['rang' => 1]);
        }
        if (!$cycle->membres()->where('user_id', $tresorier->id)->exists()) {
            $cycle->membres()->attach($tresorier->id, ['rang' => 2]);
        }
        if (!$cycle->membres()->where('user_id', $membre->id)->exists()) {
            $cycle->membres()->attach($membre->id, ['rang' => 3]);
        }
    }
}
