<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Cotisation;
use App\Models\PaiementLot;
use App\Models\Seance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaiementLotApiController extends Controller
{
    /**
     * Enregistrement d'un versement du lot (pot de tontine) à un bénéficiaire.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'seance_id' => ['required', 'exists:seances,id'],
            'user_id' => ['required', 'exists:users,id'],
            'montant' => ['required', 'numeric', 'min:1'],
        ]);

        $seance = Seance::with('cycle')->findOrFail($request->seance_id);
        $cycle = $seance->cycle;

        $totalTontineCollectee = Cotisation::whereHas('seance', fn($q) => $q->where('cycle_id', $cycle->id))
            ->where('type', 'tontine')
            ->sum('montant');

        $totalDejaVerse = PaiementLot::where('cycle_id', $cycle->id)
            ->where('statut', '!=', 'rejete')
            ->sum('montant');

        $disponibleTontine = $totalTontineCollectee - $totalDejaVerse;

        if ($disponibleTontine < $request->montant) {
            return response()->json([
                'succes' => false,
                'message' => "Fonds TONTINE insuffisants pour ce cycle. Disponible: " . number_format($disponibleTontine) . " F (Collecté: " . number_format($totalTontineCollectee) . " F, Déjà versé: " . number_format($totalDejaVerse) . " F).",
            ], 422);
        }

        // Vérification de la fréquence autorisée
        $dernierPaiement = PaiementLot::where('cycle_id', $cycle->id)
            ->where('statut', '!=', 'rejete')
            ->latest('date_paiement')
            ->first();

        if ($dernierPaiement) {
            $freq = $cycle->frequence_paiement ?? 'mensuelle';
            $datePrecedente = Carbon::parse($dernierPaiement->date_paiement);
            $prochaineDateAutorisee = ($freq === 'hebdomadaire')
                ? $datePrecedente->copy()->addWeek()
                : $datePrecedente->copy()->addMonth();

            if (now()->lt($prochaineDateAutorisee)) {
                return response()->json([
                    'succes' => false,
                    'message' => "Intervalle non respecté. Un versement précédent a eu lieu le " . $datePrecedente->format('d/m/Y') . ". Prochain versement permis le " . $prochaineDateAutorisee->format('d/m/Y') . ".",
                ], 422);
            }
        }

        return DB::transaction(function () use ($request, $seance) {
            $beneficiaire = User::findOrFail($request->user_id);

            $paiement = PaiementLot::create([
                'cycle_id' => $seance->cycle_id,
                'user_id' => $beneficiaire->id,
                'seance_id' => $seance->id,
                'montant' => $request->montant,
                'date_paiement' => now(),
                'statut' => 'en_attente',
            ]);

            Annonce::create([
                'titre' => "💰 Gain de Tontine perçu par {$beneficiaire->name} !",
                'message' => "Le membre {$beneficiaire->name} a reçu son lot de tontine d'un montant de " . number_format($request->montant) . " FCFA lors de la séance du " . $seance->date_seance->format('d/m/Y') . ".",
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'succes' => true,
                'message' => "Le versement de " . number_format($request->montant) . " F a été validé. Une annonce a été publiée.",
                'paiement' => $paiement->load(['user', 'seance', 'cycle']),
            ], 201);
        });
    }

    /**
     * Confirmation par le membre de la réception de son lot.
     */
    public function confirmer($id): JsonResponse
    {
        $paiement = PaiementLot::findOrFail($id);

        if (Auth::id() !== $paiement->user_id && Auth::user()->role !== 'admin') {
            return response()->json(['succes' => false, 'message' => 'Action non autorisée.'], 403);
        }

        $paiement->update(['statut' => 'confirme']);

        return response()->json([
            'succes' => true,
            'message' => 'Versement confirmé avec succès.',
            'paiement' => $paiement->fresh(),
        ]);
    }

    /**
     * Historique personnel des gains reçus.
     */
    public function historique(): JsonResponse
    {
        $gains = PaiementLot::where('user_id', Auth::id())
            ->with(['cycle', 'seance'])
            ->orderBy('date_paiement', 'desc')
            ->get();

        return response()->json([
            'succes' => true,
            'gains' => $gains,
        ]);
    }
}
