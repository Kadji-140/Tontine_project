<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UtilisateurApiController extends Controller
{
    /**
     * Liste des utilisateurs de la tontine avec filtres de statut et rôle.
     * Strictement borné au tenant en session et excluant les Super-Admins plateforme.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::pourTenantActuel()->orderBy('created_at', 'desc');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('statut')) {
            if ($request->statut === 'actif') {
                $query->where('is_active', true);
            } elseif ($request->statut === 'inactif') {
                $query->where('is_active', false);
            }
        }

        $utilisateurs = $query->get();

        return response()->json([
            'succes' => true,
            'utilisateurs' => $utilisateurs,
        ]);
    }

    /**
     * Basculer l'état actif/inactif (activation de compte par le bureau de la tontine).
     */
    public function basculerStatut(int $id): JsonResponse
    {
        // 1. Recherche bornée à la tontine
        $user = User::pourTenantActuel()->findOrFail($id);

        // 2. Protection contre l'auto-désactivation
        if ($user->id === Auth::id()) {
            return response()->json([
                'succes' => false,
                'message' => 'Vous ne pouvez pas modifier le statut de votre propre compte.',
            ], 422);
        }

        // 3. Sécurité absolue : protection du Super-Admin
        if ($user->est_super_admin) {
            return response()->json([
                'succes' => false,
                'message' => 'Action strictement interdite sur un compte d’administration de la plateforme SaaS.',
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $action = $user->is_active ? 'activé' : 'désactivé';

        return response()->json([
            'succes' => true,
            'message' => "Le compte de {$user->name} a été {$action}.",
            'utilisateur' => $user,
        ]);
    }

    /**
     * Statistiques de prêt pour un utilisateur donné de la tontine.
     */
    public function statsPrets(int $id): JsonResponse
    {
        $user = User::pourTenantActuel()->findOrFail($id);

        $stats = [
            'total_prets' => $user->prets()->count(),
            'prets_en_attente' => $user->prets()->where('statut', 'en_attente')->count(),
            'prets_valides' => $user->prets()->where('statut', 'valide')->count(),
            'prets_rembourses' => $user->prets()->where('statut', 'rembourse')->count(),
            'montant_total_emprunte' => (float)$user->prets()->where('statut', '!=', 'en_attente')->sum('montant_demande'),
            'montant_total_interets' => (float)$user->prets()->where('statut', '!=', 'en_attente')->sum('interet_total'),
            'prets_en_retard' => $user->prets()
                ->where('statut', 'valide')
                ->where('date_echeance', '<', now())
                ->count(),
        ];

        return response()->json([
            'succes' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Détails complets d'un membre avec historique récent.
     */
    public function details(int $id): JsonResponse
    {
        $user = User::pourTenantActuel()
            ->with(['cycles', 'cotisations.seance'])
            ->findOrFail($id);

        $stats = [
            'total_prets' => $user->prets()->count(),
            'prets_valides' => $user->prets()->where('statut', 'valide')->count(),
            'prets_rembourses' => $user->prets()->where('statut', 'rembourse')->count(),
            'montant_total_emprunte' => (float)$user->prets()->where('statut', '!=', 'en_attente')->sum('montant_demande'),
        ];

        $pretsRecents = $user->prets()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'succes' => true,
            'utilisateur' => $user,
            'stats' => $stats,
            'prets_recents' => $pretsRecents,
        ]);
    }

    /**
     * Suppression d'un utilisateur de la tontine.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::pourTenantActuel()->findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json([
                'succes' => false,
                'message' => 'Impossible de supprimer votre propre compte.',
            ], 422);
        }

        if ($user->est_super_admin) {
            return response()->json([
                'succes' => false,
                'message' => 'Action strictement interdite sur un compte d’administration de la plateforme SaaS.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'succes' => true,
            'message' => "Le compte de {$user->name} a été supprimé.",
        ]);
    }

    /**
     * Ajout direct d'un membre par l'administrateur avec génération d'identifiants provisoires.
     */
    public function creerMembre(Request $request): JsonResponse
    {
        $valides = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'role' => ['nullable', 'in:admin,tresorier,membre'],
            'mot_de_passe_personnalise' => ['nullable', 'string', 'min:6'],
        ]);

        $admin = Auth::user();
        $tenantId = $admin->tenant_id;

        if (!$tenantId) {
            return response()->json([
                'succes' => false,
                'message' => "Impossible de créer un membre sans organisation tontinière associée.",
            ], 422);
        }

        // Si aucun mot de passe n'est spécifié, génération d'un mot de passe temporaire simple et sécurisé
        $motDePasseBrut = $valides['mot_de_passe_personnalise'] ?? ('Tontine@' . rand(1000, 9999));

        $user = User::create([
            'tenant_id' => $tenantId,
            'name' => $valides['name'],
            'email' => $valides['email'],
            'phone' => $valides['phone'],
            'password' => \Illuminate\Support\Facades\Hash::make($motDePasseBrut),
            'role' => $valides['role'] ?? 'membre',
            'est_super_admin' => false,
            'status' => 'actif',
            'is_active' => true, // Immédiatement actif puisque créé directement par l'administrateur
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'succes' => true,
            'message' => "Le membre {$user->name} a été ajouté avec succès à votre tontine.",
            'utilisateur' => $user,
            'mot_de_passe_temporaire' => $motDePasseBrut,
            'identifiants' => [
                'email' => $user->email,
                'mot_de_passe' => $motDePasseBrut,
                'tontine' => $admin->tenant?->nom,
            ],
        ], 201);
    }
}
