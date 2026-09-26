<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use App\Models\Cycle;
use App\Models\Pret;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CycleApiController extends Controller
{
    /**
     * Liste de tous les cycles.
     */
    public function index(): JsonResponse
    {
        $cycles = Cycle::withCount(['membres', 'seances'])
            ->orderBy('date_debut', 'desc')
            ->get();

        return response()->json([
            'succes' => true,
            'cycles' => $cycles,
        ]);
    }

    /**
     * Détail d'un cycle avec ses séances et participants.
     */
    public function show($id): JsonResponse
    {
        $cycle = Cycle::with(['seances' => function ($q) {
            $q->orderBy('date_seance', 'asc');
        }, 'membres'])->findOrFail($id);

        return response()->json([
            'succes' => true,
            'cycle' => $cycle,
        ]);
    }

    /**
     * Création d'un cycle.
     */
    public function store(Request $request): JsonResponse
    {
        $valide = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
            'montant_part' => ['required', 'numeric', 'min:100'],
            'montant_mange_mille' => ['nullable', 'numeric', 'min:0'],
            'taux_interet' => ['required', 'numeric', 'min:0', 'max:100'],
            'frequence_paiement' => ['required', 'string', 'in:mensuelle,hebdomadaire,bimensuelle'],
            'est_actif' => ['nullable', 'boolean'],
        ]);

        if (!empty($valide['est_actif'])) {
            Cycle::query()->update(['est_actif' => false]);
        }

        $cycle = Cycle::create($valide);

        return response()->json([
            'succes' => true,
            'message' => 'Cycle créé avec succès.',
            'cycle' => $cycle,
        ], 201);
    }

    /**
     * Mise à jour d'un cycle.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $cycle = Cycle::findOrFail($id);

        $valide = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'date_debut' => ['sometimes', 'date'],
            'date_fin' => ['sometimes', 'date', 'after:date_debut'],
            'montant_part' => ['sometimes', 'numeric', 'min:100'],
            'montant_mange_mille' => ['nullable', 'numeric', 'min:0'],
            'taux_interet' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'frequence_paiement' => ['sometimes', 'string', 'in:mensuelle,hebdomadaire,bimensuelle'],
            'est_actif' => ['sometimes', 'boolean'],
        ]);

        if (isset($valide['est_actif']) && $valide['est_actif']) {
            Cycle::where('id', '!=', $cycle->id)->update(['est_actif' => false]);
        }

        $cycle->update($valide);

        return response()->json([
            'succes' => true,
            'message' => 'Cycle mis à jour avec succès.',
            'cycle' => $cycle->fresh(),
        ]);
    }

    /**
     * Suppression d'un cycle (interdite s'il possède des séances).
     */
    public function destroy($id): JsonResponse
    {
        $cycle = Cycle::findOrFail($id);

        if ($cycle->seances()->count() > 0) {
            return response()->json([
                'succes' => false,
                'message' => 'Impossible de supprimer ce cycle car il contient déjà des séances associées.',
            ], 422);
        }

        $cycle->delete();

        return response()->json([
            'succes' => true,
            'message' => 'Cycle supprimé avec succès.',
        ]);
    }

    /**
     * Activation d'un cycle.
     */
    public function activer($id): JsonResponse
    {
        $cycle = Cycle::findOrFail($id);
        Cycle::query()->update(['est_actif' => false]);
        $cycle->update(['est_actif' => true]);

        return response()->json([
            'succes' => true,
            'message' => "Le cycle {$cycle->nom} est désormais actif.",
            'cycle' => $cycle->fresh(),
        ]);
    }

    /**
     * Liste des membres du cycle et membres disponibles à l'ajout.
     */
    public function membres($id): JsonResponse
    {
        $cycle = Cycle::with('membres')->findOrFail($id);
        $membres = $cycle->membres;

        $usersDisponibles = User::where('status', 'actif')
            ->whereNotIn('id', $membres->pluck('id'))
            ->orderBy('name')
            ->get();

        return response()->json([
            'succes' => true,
            'membres' => $membres,
            'users_disponibles' => $usersDisponibles,
        ]);
    }

    /**
     * Ordre public de passage du cycle.
     */
    public function ordrePublic($id): JsonResponse
    {
        $cycle = Cycle::with(['membres' => function ($q) {
            $q->orderByPivot('rang', 'asc');
        }])->findOrFail($id);

        return response()->json([
            'succes' => true,
            'cycle' => $cycle,
            'membres' => $cycle->membres,
        ]);
    }

    /**
     * Ajout d'un membre dans le cycle.
     */
    public function ajouterMembre(Request $request, $id): JsonResponse
    {
        $cycle = Cycle::findOrFail($id);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        if ($cycle->membres()->where('user_id', $request->user_id)->exists()) {
            return response()->json([
                'succes' => false,
                'message' => 'Cet utilisateur fait déjà partie du cycle.',
            ], 422);
        }

        $maxRang = $cycle->membres()->max('rang') ?? 0;
        $cycle->membres()->attach($request->user_id, ['rang' => $maxRang + 1]);

        return response()->json([
            'succes' => true,
            'message' => 'Membre ajouté au cycle avec succès.',
            'membres' => $cycle->fresh(['membres'])->membres,
        ]);
    }

    /**
     * Retrait d'un membre du cycle.
     */
    public function retirerMembre($cycleId, $userId): JsonResponse
    {
        $cycle = Cycle::findOrFail($cycleId);
        $cycle->membres()->detach($userId);

        return response()->json([
            'succes' => true,
            'message' => 'Membre retiré du cycle avec succès.',
            'membres' => $cycle->fresh(['membres'])->membres,
        ]);
    }

    /**
     * Tirage aléatoire de l'ordre de passage.
     */
    public function melangerRangs($id): JsonResponse
    {
        $cycle = Cycle::with('membres')->findOrFail($id);

        if ($cycle->paiements()->exists()) {
            return response()->json([
                'succes' => false,
                'message' => "Impossible de modifier l'ordre : des versements ont déjà été effectués pour ce cycle.",
            ], 422);
        }

        $membres = $cycle->membres;
        if ($membres->isEmpty()) {
            return response()->json([
                'succes' => false,
                'message' => 'Aucun membre enregistré dans ce cycle.',
            ], 422);
        }

        $ids = $membres->pluck('id')->toArray();
        shuffle($ids);

        foreach ($ids as $index => $userId) {
            $cycle->membres()->updateExistingPivot($userId, ['rang' => $index + 1]);
        }

        return response()->json([
            'succes' => true,
            'message' => 'Ordre de passage tiré au sort avec succès !',
            'membres' => $cycle->fresh(['membres'])->membres,
        ]);
    }

    /**
     * Mise à jour manuelle des rangs de passage.
     */
    public function mettreAJourRangs(Request $request, $id): JsonResponse
    {
        $cycle = Cycle::findOrFail($id);

        if ($cycle->paiements()->exists()) {
            return response()->json([
                'succes' => false,
                'message' => "Impossible de modifier l'ordre : des versements ont déjà démarré.",
            ], 422);
        }

        $request->validate([
            'rangs' => ['required', 'array'],
            'rangs.*' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $cycle) {
            foreach ($request->rangs as $userId => $rang) {
                $cycle->membres()->updateExistingPivot($userId, ['rang' => $rang]);
            }
        });

        return response()->json([
            'succes' => true,
            'message' => 'Ordre des rangs enregistré avec succès.',
            'membres' => $cycle->fresh(['membres'])->membres,
        ]);
    }

    /**
     * Calcul de la distribution des bénéfices d'épargne (BANQUE).
     */
    public function distribution($id): JsonResponse
    {
        $cycle = Cycle::findOrFail($id);

        $totalAssiette = Cotisation::whereHas('seance', fn($q) => $q->where('cycle_id', $cycle->id))
            ->where('type', 'banque')
            ->sum('montant');

        $totalBenefice = Pret::whereHas('seance', fn($q) => $q->where('cycle_id', $cycle->id))
            ->whereIn('statut', ['valide', 'rembourse'])
            ->sum('interet_total');

        $tauxRendementReel = ($totalAssiette > 0) ? (($totalAssiette + $totalBenefice) / $totalAssiette) : 0;

        $membresIds = Cotisation::whereHas('seance', fn($q) => $q->where('cycle_id', $cycle->id))
            ->where('type', 'banque')
            ->distinct()
            ->pluck('user_id');

        $repartition = [];
        foreach ($membresIds as $uId) {
            $user = User::find($uId);
            if (!$user) continue;

            $apport = Cotisation::whereHas('seance', fn($q) => $q->where('cycle_id', $cycle->id))
                ->where('user_id', $uId)
                ->where('type', 'banque')
                ->sum('montant');

            $pourcentage = ($totalAssiette > 0) ? ($apport / $totalAssiette) : 0;
            $totalAPercevoir = $apport * $tauxRendementReel;
            $gain = $totalAPercevoir - $apport;

            $repartition[] = [
                'user' => $user,
                'apport' => $apport,
                'pourcentage' => round($pourcentage * 100, 2),
                'gain' => $gain,
                'total_a_percevoir' => $totalAPercevoir,
            ];
        }

        usort($repartition, fn($a, $b) => strcasecmp($a['user']->name, $b['user']->name));

        return response()->json([
            'succes' => true,
            'cycle' => $cycle,
            'total_assiette' => $totalAssiette,
            'total_benefice' => $totalBenefice,
            'taux_rendement_reel' => $tauxRendementReel,
            'repartition' => $repartition,
        ]);
    }
}
