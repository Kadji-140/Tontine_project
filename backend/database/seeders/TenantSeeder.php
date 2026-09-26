<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Crée les organisations (tontines clientes) de la plateforme SaaS.
     */
    public function run(): void
    {
        // Organisation 1 : Yaoundé
        Tenant::firstOrCreate(
            ['slug' => 'solidarite-yaounde'],
            [
                'nom' => 'Tontine Solidarité Yaoundé',
                'statut' => 'actif',
                'devise' => 'FCFA',
                'description' => 'Association fraternelle des cadres et entrepreneurs de Yaoundé (Bastos - Omnisports).',
                'configuration' => [
                    'taux_interet_defaut' => 10,
                    'mange_mille_actif' => true,
                    'frequence_defaut' => 'mensuelle',
                    'ville' => 'Yaoundé',
                    'pays' => 'Cameroun',
                ],
            ]
        );

        // Organisation 2 : Douala
        Tenant::firstOrCreate(
            ['slug' => 'investissement-douala'],
            [
                'nom' => 'Cercle d’Investissement Douala',
                'statut' => 'actif',
                'devise' => 'FCFA',
                'description' => 'Cercle d’épargne rotative et de financement solidaire pour commerçants et PME (Akwa - Bonanjo).',
                'configuration' => [
                    'taux_interet_defaut' => 5,
                    'mange_mille_actif' => true,
                    'frequence_defaut' => 'mensuelle',
                    'ville' => 'Douala',
                    'pays' => 'Cameroun',
                ],
            ]
        );

        // Organisation 3 : Diaspora (Optionnelle pour démo multidevise)
        Tenant::firstOrCreate(
            ['slug' => 'diaspora-paris-bafoussam'],
            [
                'nom' => 'Mutuelle Diaspora Ouest-Paris',
                'statut' => 'actif',
                'devise' => 'EUR',
                'description' => 'Tontine de la diaspora pour les projets de développement communautaire.',
                'configuration' => [
                    'taux_interet_defaut' => 3,
                    'mange_mille_actif' => false,
                    'frequence_defaut' => 'mensuelle',
                    'ville' => 'Paris / Bafoussam',
                    'pays' => 'France / Cameroun',
                ],
            ]
        );
    }
}
