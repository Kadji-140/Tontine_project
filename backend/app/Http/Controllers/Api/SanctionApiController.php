<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sanction;
use App\Models\Seance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SanctionApiController extends Controller
{
    /**
     * Liste de toutes les sanctions.
     */
    public function index(): JsonResponse
    {
        $sanctions = Sanction::with(['user', 'seance'])
            ->orderBy('est_reglee', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'succes' => true,
            'sanctions' => $sanctions,
        ]);
    }

    /**
     * Enregistrement d'une sanction.
     */
    public function store(Request $request): JsonResponse
    {
        $valide = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'montant' => ['required', 'numeric', 'min:100'],
            'motif' => ['required', 'string', 'max:255'],
        ]);

        $sanction = Sanction::create([
            'user_id' => $valide['user_id'],
            'montant' => $valide['montant'],
            'motif' => $valide['motif'],
            'est_reglee' => false,
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Sanction enregistrée avec succès.',
            'sanction' => $sanction->load('user'),
        ], 201);
    }

    /**
     * Paiement d'une sanction et entrée de l'argent dans la séance ouverte.
     */
    public function marquerPayee($id): JsonResponse
    {
        $sanction = Sanction::findOrFail($id);

        if ($sanction->est_reglee) {
            return response()->json(['succes' => false, 'message' => 'Cette sanction a déjà été réglée.'], 422);
        }

        $seance = Seance::where('statut', 'ouverte')->latest('date_seance')->first();
        if (!$seance) {
            return response()->json(['succes' => false, 'message' => "Aucune séance ouverte pour encaisser l'amende."], 422);
        }

        DB::transaction(function () use ($sanction, $seance) {
            $sanction->update([
                'est_reglee' => true,
                'seance_id' => $seance->id,
            ]);

            $seance->total_encaisse += $sanction->montant;
            $seance->save();
        });

        return response()->json([
            'succes' => true,
            'message' => 'Sanction réglée et ajoutée à la caisse de séance.',
            'sanction' => $sanction->fresh(['user', 'seance']),
            'total_encaisse_seance' => $seance->total_encaisse,
        ]);
    }

    /**
     * Suppression d'une sanction non réglée.
     */
    public function destroy($id): JsonResponse
    {
        $sanction = Sanction::findOrFail($id);

        if ($sanction->est_reglee) {
            return response()->json(['succes' => false, 'message' => 'Impossible de supprimer une sanction déjà réglée.'], 422);
        }

        $sanction->delete();

        return response()->json([
            'succes' => true,
            'message' => 'Sanction annulée avec succès.',
        ]);
    }
}
