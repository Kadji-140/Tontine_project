<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Point d'entrée principal des seeders de la plateforme SaaS TontinePro.
     */
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,      // 1. Création des organisations / tontines SaaS
            UserSeeder::class,        // 2. Utilisateurs avec profils réels camerounais
            TontineDataSeeder::class, // 3. Données transactionnelles complètes (séances, cotisations, prêts, sanctions, dépenses, etc.)
        ]);
    }
}
