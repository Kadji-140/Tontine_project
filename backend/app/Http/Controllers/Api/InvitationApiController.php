<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InvitationApiController extends Controller
{
    /**
     * Génère un lien d'invitation sécurisé et unique pour la tontine (Zone Bureau).
     */
    public function generer(Request $request): JsonResponse
    {
        $valides = $request->validate([
            'email' => ['nullable', 'email'],
            'role' => ['nullable', Rule::in(['membre', 'tresorier'])],
            'jours_validite' => ['nullable', 'integer', 'min:1', 'max:30'],
            'max_utilisations' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant || $tenant->statut !== 'actif') {
            return response()->json([
                'succes' => false,
                'message' => "Votre tontine n'est pas autorisée à émettre des invitations.",
            ], 403);
        }

        // Token cryptographique sécurisé de 64 caractères hexadécimaux
        $token = bin2hex(random_bytes(32));
        $jours = $valides['jours_validite'] ?? 7;

        $invitation = Invitation::create([
            'tenant_id' => $tenant->id,
            'token' => $token,
            'email' => $valides['email'] ?? null,
            'role' => $valides['role'] ?? 'membre',
            'max_utilisations' => $valides['max_utilisations'] ?? 1,
            'nb_utilisations' => 0,
            'expire_le' => now()->addDays($jours),
            'cree_par' => $user->id,
        ]);

        // URL frontend d'adhésion scellée par le token
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $lienAdhesion = rtrim($frontendUrl, '/') . "/inscription?invitation={$token}";

        return response()->json([
            'succes' => true,
            'message' => "Lien d'invitation généré avec succès.",
            'invitation' => $invitation,
            'lien' => $lienAdhesion,
            'tontine' => [
                'nom' => $tenant->nom,
                'slug' => $tenant->slug,
            ],
        ], 201);
    }

    /**
     * Liste des invitations émises par le bureau pour cette tontine.
     */
    public function index(): JsonResponse
    {
        $invitations = Invitation::with('createur:id,name')
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($inv) {
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                return [
                    'id' => $inv->id,
                    'token' => $inv->token,
                    'email' => $inv->email,
                    'role' => $inv->role,
                    'max_utilisations' => $inv->max_utilisations,
                    'nb_utilisations' => $inv->nb_utilisations,
                    'est_valide' => $inv->estValide(),
                    'expire_le' => $inv->expire_le->toIso8601String(),
                    'cree_par' => $inv->createur?->name,
                    'created_at' => $inv->created_at->toIso8601String(),
                    'lien' => rtrim($frontendUrl, '/') . "/inscription?invitation={$inv->token}",
                ];
            });

        return response()->json([
            'succes' => true,
            'invitations' => $invitations,
        ]);
    }

    /**
     * Révocation d'un lien d'invitation.
     */
    public function revoquer(int $id): JsonResponse
    {
        $invitation = Invitation::findOrFail($id);
        $invitation->delete();

        return response()->json([
            'succes' => true,
            'message' => "L'invitation a été révoquée et ne pourra plus être utilisée.",
        ]);
    }

    /**
     * Vérification publique d'un token d'invitation (Sans authentification).
     * Garantit l'authenticité de la tontine sans possibilité de falsification d'ID.
     */
    public function verifierPublic(string $token): JsonResponse
    {
        $invitation = Invitation::withoutGlobalScope('tenant')
            ->with(['tenant' => fn($q) => $q->select('id', 'nom', 'slug', 'devise', 'statut')])
            ->where('token', $token)
            ->first();

        if (!$invitation || !$invitation->estValide()) {
            return response()->json([
                'succes' => false,
                'valide' => false,
                'message' => "Ce lien d'invitation est invalide, a déjà été utilisé ou a expiré. Veuillez contacter l'administrateur de votre tontine.",
            ], 404);
        }

        if ($invitation->tenant->statut !== 'actif') {
            return response()->json([
                'succes' => false,
                'valide' => false,
                'message' => "L'organisation émettrice de cette invitation est actuellement suspendue.",
            ], 403);
        }

        return response()->json([
            'succes' => true,
            'valide' => true,
            'tontine' => [
                'nom' => $invitation->tenant->nom,
                'slug' => $invitation->tenant->slug,
                'devise' => $invitation->tenant->devise,
            ],
            'role' => $invitation->role,
            'email_restreint' => $invitation->email,
            'expire_le' => $invitation->expire_le->toIso8601String(),
        ]);
    }
}
