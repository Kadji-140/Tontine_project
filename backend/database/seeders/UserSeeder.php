<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Crée des profils camerounais réalistes pour le Super-Admin et chaque tontine.
     */
    public function run(): void
    {
        $passwordHash = Hash::make('password');

        // =========================================================================
        // 1. SUPER-ADMINISTRATEUR (Propriétaire de la plateforme SaaS)
        // =========================================================================
        User::firstOrCreate(
            ['email' => 'superadmin@tontine.test'],
            [
                'tenant_id' => null,
                'name' => 'Ferdinand Nguema (Directeur SaaS)',
                'phone' => '+237690000000',
                'password' => $passwordHash,
                'role' => 'admin',
                'est_super_admin' => true,
                'status' => 'actif',
                'is_active' => true,
                'profession' => 'Ingénieur Plateforme & Cloud',
                'adresse' => 'Bastos, Yaoundé',
                'cni' => '100000001',
                'beneficiaire' => 'Ayants-droit Nguema',
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 2. MEMBRES TONTINE 1 : Tontine Solidarité Yaoundé
        // =========================================================================
        $t1 = Tenant::where('slug', 'solidarite-yaounde')->firstOrFail();

        $membresT1 = [
            [
                'email' => 'admin@tontine.test',
                'name' => 'Jean-Paul Kamga',
                'phone' => '+237699112233',
                'role' => 'admin',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Ingénieur Télécoms',
                'adresse' => 'Omnisports, Yaoundé',
                'cni' => '100234567',
                'beneficiaire' => 'Thérèse Kamga (Épouse)',
            ],
            [
                'email' => 'tresorier@tontine.test',
                'name' => 'Chantal Ngono',
                'phone' => '+237677445566',
                'role' => 'tresorier',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Expert-Comptable Agréée',
                'adresse' => 'Mvan, Yaoundé',
                'cni' => '100876543',
                'beneficiaire' => 'Junior Ngono (Fils)',
            ],
            [
                'email' => 'membre@tontine.test',
                'name' => 'Samuel Eto\'o Mbarga',
                'phone' => '+237691223344',
                'role' => 'membre',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Consultant en Stratégie',
                'adresse' => 'Bastos, Yaoundé',
                'cni' => '101234890',
                'beneficiaire' => 'Georgette Mbarga (Épouse)',
            ],
            [
                'email' => 'brigitte@tontine.test',
                'name' => 'Brigitte Mengue',
                'phone' => '+237695334455',
                'role' => 'membre',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Pharmacienne Titulaire',
                'adresse' => 'Biyem-Assi, Yaoundé',
                'cni' => '102345678',
                'beneficiaire' => 'Dr. Mengue Pierre',
            ],
            [
                'email' => 'patrick@tontine.test',
                'name' => 'Patrick Fotso',
                'phone' => '+237670445566',
                'role' => 'membre',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Architecte Urbaniste',
                'adresse' => 'Santa Barbara, Yaoundé',
                'cni' => '103456789',
                'beneficiaire' => 'Aline Fotso (Sœur)',
            ],
            [
                'email' => 'marie@tontine.test',
                'name' => 'Marie-Claire Abena',
                'phone' => '+237698556677',
                'role' => 'membre',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Magistrat',
                'adresse' => 'Mimboman, Yaoundé',
                'cni' => '104567890',
                'beneficiaire' => 'Arnaud Abena (Fils)',
            ],
            [
                'email' => 'alain@tontine.test',
                'name' => 'Alain Kenmogne',
                'phone' => '+237672667788',
                'role' => 'membre',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Entrepreneur BTP',
                'adresse' => 'Nsimeyong, Yaoundé',
                'cni' => '105678901',
                'beneficiaire' => 'Clarisse Kenmogne (Épouse)',
            ],
            // Sas d'attente (postulant en attente d'approbation)
            [
                'email' => 'nouveau@tontine.test',
                'name' => 'Christian Tagne',
                'phone' => '+237693778899',
                'role' => 'membre',
                'est_super_admin' => false,
                'is_active' => false,
                'profession' => 'Commerçant Grossiste',
                'adresse' => 'Mokolo, Yaoundé',
                'cni' => '106789012',
                'beneficiaire' => 'Serge Tagne (Frère)',
            ],
        ];

        foreach ($membresT1 as $m) {
            User::firstOrCreate(
                ['email' => $m['email']],
                array_merge($m, [
                    'tenant_id' => $t1->id,
                    'password' => $passwordHash,
                    'status' => 'actif',
                    'email_verified_at' => now(),
                ])
            );
        }

        // =========================================================================
        // 3. MEMBRES TONTINE 2 : Cercle d’Investissement Douala
        // =========================================================================
        $t2 = Tenant::where('slug', 'investissement-douala')->firstOrFail();

        $membresT2 = [
            [
                'email' => 'admin2@tontine.test',
                'name' => 'Serge Essomba',
                'phone' => '+237694111222',
                'role' => 'admin',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Directeur Général Logistique',
                'adresse' => 'Bonanjo, Douala',
                'cni' => '200111222',
                'beneficiaire' => 'Danielle Essomba (Épouse)',
            ],
            [
                'email' => 'tresorier2@tontine.test',
                'name' => 'Nadège Tchoumbou',
                'phone' => '+237675222333',
                'role' => 'tresorier',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Auditrice Financière Senior',
                'adresse' => 'Akwa, Douala',
                'cni' => '200222333',
                'beneficiaire' => 'Marc Tchoumbou (Frère)',
            ],
            [
                'email' => 'eric@tontine.test',
                'name' => 'Eric Ndongo',
                'phone' => '+237696333444',
                'role' => 'membre',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Transitaire en Douane',
                'adresse' => 'Deido, Douala',
                'cni' => '200333444',
                'beneficiaire' => 'Carine Ndongo (Épouse)',
            ],
            [
                'email' => 'danielle@tontine.test',
                'name' => 'Danielle Mballa',
                'phone' => '+237678444555',
                'role' => 'membre',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Notaire Associée',
                'adresse' => 'Bonapriso, Douala',
                'cni' => '200444555',
                'beneficiaire' => 'Maître Mballa Père',
            ],
            [
                'email' => 'fabrice@tontine.test',
                'name' => 'Fabrice Kuate',
                'phone' => '+237699555666',
                'role' => 'membre',
                'est_super_admin' => false,
                'is_active' => true,
                'profession' => 'Directeur Commercial Import-Export',
                'adresse' => 'Makepe, Douala',
                'cni' => '200555666',
                'beneficiaire' => 'Sylvie Kuate (Épouse)',
            ],
        ];

        foreach ($membresT2 as $m) {
            User::firstOrCreate(
                ['email' => $m['email']],
                array_merge($m, [
                    'tenant_id' => $t2->id,
                    'password' => $passwordHash,
                    'status' => 'actif',
                    'email_verified_at' => now(),
                ])
            );
        }
    }
}
