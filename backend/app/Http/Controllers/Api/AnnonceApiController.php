<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnonceApiController extends Controller
{
    /**
     * Liste des annonces visibles par l'utilisateur connecté.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $annonces = Annonce::with('author')
            ->where(function ($q) use ($user) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', $user->role);
            })
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($annonce) use ($user) {
                return [
                    'id' => $annonce->id,
                    'titre' => $annonce->titre,
                    'message' => $annonce->message,
                    'auteur' => $annonce->author?->name ?? 'Système',
                    'created_at' => $annonce->created_at->format('d/m/Y H:i'),
                    'est_lue' => $annonce->isReadBy($user),
                ];
            });

        return response()->json([
            'succes' => true,
            'annonces' => $annonces,
        ]);
    }

    /**
     * Création d'une annonce.
     */
    public function store(Request $request): JsonResponse
    {
        $valide = $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
            'target_role' => ['nullable', 'string', 'in:admin,tresorier,membre'],
        ]);

        $annonce = Annonce::create([
            'titre' => $valide['titre'],
            'message' => $valide['message'],
            'user_id' => Auth::id(),
            'target_role' => $valide['target_role'] ?? null,
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Annonce diffusée avec succès.',
            'annonce' => $annonce,
        ], 201);
    }

    /**
     * Marquer une annonce comme lue par l'utilisateur.
     */
    public function marquerLue($id): JsonResponse
    {
        $annonce = Annonce::findOrFail($id);
        $user = Auth::user();

        if (!$annonce->isReadBy($user)) {
            $annonce->readers()->attach($user->id);
        }

        return response()->json([
            'succes' => true,
            'message' => 'Annonce marquée comme lue.',
        ]);
    }

    /**
     * Suppression d'une annonce.
     */
    public function destroy($id): JsonResponse
    {
        $annonce = Annonce::findOrFail($id);
        $annonce->delete();

        return response()->json([
            'succes' => true,
            'message' => 'Annonce supprimée avec succès.',
        ]);
    }
}
