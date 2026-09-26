<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Cycle;
use App\Models\Cotisation;
use App\Models\Pret;
use App\Models\Remboursement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller
{
    /**
     * Indicateurs clés globaux et transversaux de l'écosystème SaaS.
     */
    public function statistiquesGlobales(): JsonResponse
    {
        // 1. Tontines
        $totalTontines = Tenant::count();
        $tontinesActives = Tenant::where('statut', 'actif')->count();
        $tontinesSuspendues = Tenant::where('statut', 'suspendu')->count();

        // 2. Utilisateurs
        $totalUtilisateurs = User::where('est_super_admin', false)->count();
        $utilisateursActifs = User::where('est_super_admin', false)->where('is_active', true)->count();
        $utilisateursEnAttente = User::where('est_super_admin', false)->where('is_active', false)->count();

        // 3. Métriques financières consolidées (sans scope tenant)
        $totalEpargne = (float) Cotisation::withoutGlobalScope('tenant')->sum('montant');
        $totalPretsAccordés = (float) Pret::withoutGlobalScope('tenant')
            ->whereIn('statut', ['valide', 'rembourse'])
            ->sum('montant_demande');
        $totalRembourse = (float) Remboursement::withoutGlobalScope('tenant')->sum('montant');

        $pretsActifs = Pret::withoutGlobalScope('tenant')
            ->where('statut', 'valide')
            ->with('remboursements')
            ->get();
        $encoursCredits = (float) $pretsActifs->sum(fn($p) => $p->reste_a_payer);

        // 4. Dernières tontines créées
        $tontinesRecentes = Tenant::withCount(['utilisateurs', 'cycles'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'succes' => true,
            'kpis' => [
                'total_tontines' => $totalTontines,
                'tontines_actives' => $tontinesActives,
                'tontines_suspendues' => $tontinesSuspendues,
                'total_utilisateurs' => $totalUtilisateurs,
                'utilisateurs_actifs' => $utilisateursActifs,
                'utilisateurs_en_attente' => $utilisateursEnAttente,
                'total_epargne_plateforme' => $totalEpargne,
                'total_prets_accordes' => $totalPretsAccordés,
                'total_rembourse' => $totalRembourse,
                'encours_credits' => $encoursCredits,
            ],
            'tontines_recentes' => $tontinesRecentes,
        ]);
    }

    /**
     * Liste complète des tontines avec recherche et filtres.
     */
    public function listeTenants(Request $request): JsonResponse
    {
        $query = Tenant::withCount(['utilisateurs', 'cycles']);

        if ($request->filled('recherche')) {
            $r = $request->string('recherche');
            $query->where(function ($q) use ($r) {
                $q->where('nom', 'like', "%{$r}%")
                  ->orWhere('slug', 'like', "%{$r}%")
                  ->orWhere('description', 'like', "%{$r}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }

        $tenants = $query->latest()->paginate(15);

        // Enrichir chaque tenant avec le cycle actif et l'administrateur
        $tenants->getCollection()->transform(function ($tenant) {
            $cycleActif = Cycle::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('est_actif', true)
                ->first(['id', 'nom', 'montant_part', 'date_debut', 'date_fin']);

            $adminPrincipal = User::where('tenant_id', $tenant->id)
                ->where('role', 'admin')
                ->first(['id', 'name', 'email', 'phone']);

            $totalEpargne = Cotisation::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->sum('montant');

            $tenant->cycle_actif = $cycleActif;
            $tenant->admin_principal = $adminPrincipal;
            $tenant->total_epargne = (float)$totalEpargne;

            return $tenant;
        });

        return response()->json([
            'succes' => true,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Création d'une nouvelle tontine par le Super-Admin avec son premier administrateur.
     */
    public function creerTenant(Request $request): JsonResponse
    {
        $valides = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'unique:tenants,slug', 'regex:/^[a-z0-9\-]+$/'],
            'devise' => ['required', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:1000'],
            // Administrateur référent
            'admin_nom' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'unique:users,email'],
            'admin_phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        return DB::transaction(function () use ($valides) {
            $tenant = Tenant::create([
                'nom' => $valides['nom'],
                'slug' => strtolower($valides['slug']),
                'devise' => strtoupper($valides['devise']),
                'description' => $valides['description'] ?? null,
                'statut' => 'actif',
            ]);

            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $valides['admin_nom'],
                'email' => $valides['admin_email'],
                'phone' => $valides['admin_phone'],
                'password' => Hash::make($valides['admin_password']),
                'role' => 'admin',
                'est_super_admin' => false,
                'status' => 'actif',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            return response()->json([
                'succes' => true,
                'message' => "La tontine '{$tenant->nom}' et son compte administrateur ont été créés avec succès.",
                'tenant' => $tenant,
                'admin' => $admin,
            ], 201);
        });
    }

    /**
     * Consultation détaillée d'une tontine spécifique.
     */
    public function detailsTenant(int $id): JsonResponse
    {
        $tenant = Tenant::with(['cycles' => fn($q) => $q->withoutGlobalScope('tenant')->latest()])
            ->findOrFail($id);

        $membres = User::where('tenant_id', $tenant->id)->latest()->get();

        $statistiques = [
            'total_membres' => $membres->count(),
            'membres_actifs' => $membres->where('is_active', true)->count(),
            'total_epargne' => (float) Cotisation::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->sum('montant'),
            'total_prets' => (float) Pret::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->sum('montant_demande'),
            'total_rembourse' => (float) Remboursement::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->sum('montant'),
        ];

        return response()->json([
            'succes' => true,
            'tenant' => $tenant,
            'membres' => $membres,
            'statistiques' => $statistiques,
        ]);
    }

    /**
     * Bascule le statut d'une tontine (Activer / Suspendre).
     */
    public function basculerStatutTenant(int $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $nouveauStatut = $tenant->statut === 'actif' ? 'suspendu' : 'actif';
        $tenant->update(['statut' => $nouveauStatut]);

        $message = $nouveauStatut === 'suspendu'
            ? "L'organisation '{$tenant->nom}' a été suspendue. Les membres ne pourront plus accéder à l'application."
            : "L'organisation '{$tenant->nom}' a été réactivée avec succès.";

        return response()->json([
            'succes' => true,
            'message' => $message,
            'statut' => $nouveauStatut,
            'tenant' => $tenant,
        ]);
    }

    /**
     * Liste transversale de tous les utilisateurs de la plateforme.
     */
    public function listeUtilisateurs(Request $request): JsonResponse
    {
        $query = User::with('tenant');

        if ($request->filled('recherche')) {
            $r = $request->string('recherche');
            $query->where(function ($q) use ($r) {
                $q->where('name', 'like', "%{$r}%")
                  ->orWhere('email', 'like', "%{$r}%")
                  ->orWhere('phone', 'like', "%{$r}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->integer('tenant_id'));
        }

        $utilisateurs = $query->latest()->paginate(20);

        return response()->json([
            'succes' => true,
            'utilisateurs' => $utilisateurs,
        ]);
    }

    /**
     * Attribue ou révoque le statut Super-Admin à un utilisateur.
     */
    public function promouvoirSuperAdmin(int $id, Request $request): JsonResponse
    {
        $user = User::findOrFail($id);

        // Ne peut pas se révoquer soi-même
        if ($user->id === auth()->id() && !$request->boolean('est_super_admin')) {
            return response()->json([
                'succes' => false,
                'message' => "Vous ne pouvez pas révoquer vos propres privilèges de Super-Administrateur.",
            ], 422);
        }

        $estSuper = $request->boolean('est_super_admin');
        $user->update(['est_super_admin' => $estSuper]);

        return response()->json([
            'succes' => true,
            'message' => $estSuper
                ? "L'utilisateur {$user->name} a été promu Super-Administrateur de la plateforme."
                : "Les privilèges Super-Administrateur de {$user->name} ont été retirés.",
            'utilisateur' => $user,
        ]);
    }

    /**
     * Modification du rôle ou activation d'un utilisateur au sein de sa tontine.
     */
    public function modifierUtilisateur(int $id, Request $request): JsonResponse
    {
        $valides = $request->validate([
            'role' => ['sometimes', Rule::in(['admin', 'tresorier', 'membre'])],
            'is_active' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['actif', 'suspendu'])],
        ]);

        $user = User::findOrFail($id);
        $user->update($valides);

        return response()->json([
            'succes' => true,
            'message' => "Profil de l'utilisateur mis à jour avec succès.",
            'utilisateur' => $user,
        ]);
    }
}
