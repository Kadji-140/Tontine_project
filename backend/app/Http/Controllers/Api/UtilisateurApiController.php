<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pret;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UtilisateurApiController extends Controller
{
    /**
     * Liste des utilisateurs avec filtres de statut et rôle.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::orderBy('created_at', 'desc');

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
     * Basculer l'état actif/inactif (activation de compte par le bureau).
     */
    public function basculerStatut($id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json([
                'succes' => false,
                'message' => 'Vous ne pouvez pas modifier le statut de votre propre compte.',
            ], 422);
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
     * Statistiques de prêt pour un utilisateur donné.
     */
    public function statsPrets($id): JsonResponse
    {
        $user = User::findOrFail($id);

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
    public function details($id): JsonResponse
    {
        $user = User::with(['cycles', 'cotisations.seance'])->findOrFail($id);

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
     * Suppression d'un utilisateur.
     */
    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json([
                'succes' => false,
                'message' => 'Impossible de supprimer votre propre compte.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'succes' => true,
            'message' => "Le compte de {$user->name} a été supprimé.",
        ]);
    }
}
