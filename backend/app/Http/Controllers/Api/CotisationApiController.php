<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use App\Models\FondsDepense;
use App\Models\Seance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CotisationApiController extends Controller
{
    /**
     * Enregistrement d'une cotisation avec déduction automatique du Mange-Mille si requis.
     */
    public function store(Request $request): JsonResponse
    {
        $valide = $request->validate([
            'seance_id' => ['required', 'exists:seances,id'],
            'user_id' => ['required', 'exists:users,id'],
            'montant' => ['required', 'numeric', 'min:1'],
            'type' => ['required', 'string', 'in:tontine,secours,autre'],
        ]);

        return DB::transaction(function () use ($valide, $request) {
            $seance = Seance::with('cycle')->findOrFail($valide['seance_id']);
            $cycle = $seance->cycle;

            // Gestion du "Mange-Mille" (frais de collation/séance)
            $dejaPaye = FondsDepense::where('seance_id', $seance->id)
                ->where('user_id', $valide['user_id'])
                ->where('type', 'mange_mille')
                ->exists();

            $fraisMangeMille = 0;
            $montantFinalCotisation = $valide['montant'];

            if (!$dejaPaye && $cycle && $cycle->montant_mange_mille > 0) {
                $montantMangeMille = (float)$cycle->montant_mange_mille;

                if ($valide['montant'] < $montantMangeMille) {
                    return response()->json([
                        'succes' => false,
                        'message' => "Le montant versé (" . number_format($valide['montant']) . " F) est inférieur au Mange-Mille obligatoire de " . number_format($montantMangeMille) . " F."
                    ], 422);
                }

                FondsDepense::create([
                    'cycle_id' => $cycle->id,
                    'seance_id' => $seance->id,
                    'user_id' => $valide['user_id'],
                    'type' => 'mange_mille',
                    'montant' => $montantMangeMille,
                    'description' => 'Frais de séance Mange-Mille (automatique)',
                ]);

                $montantFinalCotisation -= $montantMangeMille;
                $fraisMangeMille = $montantMangeMille;
            }

            $cotisation = null;
            if ($montantFinalCotisation > 0) {
                $cotisation = Cotisation::create([
                    'seance_id' => $seance->id,
                    'user_id' => $valide['user_id'],
                    'montant' => $montantFinalCotisation,
                    'type' => $valide['type'],
                    'enregistre_par' => Auth::id(),
                ]);
            }

            // Mise à jour de la caisse physique de la séance
            $seance->total_encaisse += $valide['montant'];
            $seance->save();

            $message = 'Cotisation enregistrée avec succès.';
            if ($fraisMangeMille > 0) {
                $message .= ' (Dont ' . number_format($fraisMangeMille) . ' F de Mange-Mille prélevés)';
            }

            return response()->json([
                'succes' => true,
                'message' => $message,
                'cotisation' => $cotisation,
                'frais_mange_mille' => $fraisMangeMille,
                'total_encaisse_seance' => $seance->total_encaisse,
            ], 201);
        });
    }

    /**
     * Annulation d'une cotisation et ajustement du solde de caisse.
     */
    public function destroy($id): JsonResponse
    {
        $cotisation = Cotisation::findOrFail($id);

        return DB::transaction(function () use ($cotisation) {
            $seance = $cotisation->seance;
            $seance->total_encaisse -= $cotisation->montant;
            $seance->save();

            $cotisation->delete();

            return response()->json([
                'succes' => true,
                'message' => 'Cotisation annulée avec succès et solde de séance ajusté.',
                'total_encaisse_seance' => $seance->total_encaisse,
            ]);
        });
    }
}
