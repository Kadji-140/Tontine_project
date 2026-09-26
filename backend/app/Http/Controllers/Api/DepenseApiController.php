<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use App\Models\Depense;
use App\Models\FondsDepense;
use App\Models\Pret;
use App\Models\Remboursement;
use App\Models\Seance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepenseApiController extends Controller
{
    /**
     * Enregistrement d'une nouvelle dépense (en attente par défaut).
     */
    public function store(Request $request): JsonResponse
    {
        $valide = $request->validate([
            'seance_id' => ['required', 'exists:seances,id'],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:100'],
        ]);

        return DB::transaction(function () use ($valide) {
            $seance = Seance::findOrFail($valide['seance_id']);

            // Vérification de la solvabilité de la caisse globale
            $soldeGlobal = (
                Cotisation::sum('montant') +
                Remboursement::sum('montant') +
                FondsDepense::sum('montant')
            ) - (
                Pret::where('statut', 'valide')->sum('montant_demande') +
                Depense::where('statut', 'validee')->sum('montant')
            );

            if ($soldeGlobal < $valide['montant']) {
                return response()->json([
                    'succes' => false,
                    'message' => "Fonds insuffisants en caisse globale (" . number_format($soldeGlobal) . " F disponibles).",
                ], 422);
            }

            $depense = Depense::create([
                'seance_id' => $seance->id,
                'enregistre_par' => Auth::id(),
                'motif' => $valide['motif'],
                'montant' => $valide['montant'],
                'statut' => 'en_attente',
            ]);

            return response()->json([
                'succes' => true,
                'message' => 'Dépense enregistrée et mise en attente de validation.',
                'depense' => $depense,
            ], 201);
        });
    }

    /**
     * Validation d'une dépense par l'administrateur (débit de la caisse).
     */
    public function valider($id): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['succes' => false, 'message' => 'Action réservée aux administrateurs.'], 403);
        }

        $depense = Depense::findOrFail($id);

        if ($depense->statut === 'validee') {
            return response()->json(['succes' => false, 'message' => 'Cette dépense est déjà validée.'], 422);
        }

        return DB::transaction(function () use ($depense) {
            $seance = $depense->seance;

            if ($seance->total_encaisse < $depense->montant) {
                return response()->json([
                    'succes' => false,
                    'message' => 'Impossible de valider : la caisse de la séance est insuffisante.',
                ], 422);
            }

            $seance->total_encaisse -= $depense->montant;
            $seance->save();

            $depense->update(['statut' => 'validee']);

            return response()->json([
                'succes' => true,
                'message' => 'Dépense validée et débitée de la séance.',
                'depense' => $depense->fresh(),
                'total_encaisse_seance' => $seance->total_encaisse,
            ]);
        });
    }

    /**
     * Rejet d'une dépense.
     */
    public function rejeter($id): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['succes' => false, 'message' => 'Action réservée aux administrateurs.'], 403);
        }

        $depense = Depense::findOrFail($id);

        if ($depense->statut === 'validee') {
            return response()->json(['succes' => false, 'message' => 'Impossible de rejeter une dépense déjà validée.'], 422);
        }

        $depense->update(['statut' => 'rejetee']);

        return response()->json([
            'succes' => true,
            'message' => 'Dépense rejetée avec succès.',
            'depense' => $depense->fresh(),
        ]);
    }

    /**
     * Suppression / annulation d'une dépense.
     */
    public function destroy($id): JsonResponse
    {
        $depense = Depense::findOrFail($id);

        return DB::transaction(function () use ($depense) {
            $seance = $depense->seance;

            if ($depense->statut === 'validee') {
                $seance->total_encaisse += $depense->montant;
                $seance->save();
            }

            $depense->delete();

            return response()->json([
                'succes' => true,
                'message' => 'Dépense supprimée avec succès.',
                'total_encaisse_seance' => $seance->total_encaisse,
            ]);
        });
    }
}
