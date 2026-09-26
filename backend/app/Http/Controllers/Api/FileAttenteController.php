<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileAttenteController extends Controller
{
    /**
     * File d'attente (ordre de passage et séances prévisionnelles).
     */
    public function index($cycleId): JsonResponse
    {
        $cycle = Cycle::with(['membres' => function ($q) {
            $q->orderByPivot('rang', 'asc');
        }])->findOrFail($cycleId);

        $membres = $cycle->membres;

        $seancesFutures = $cycle->seances()
            ->where('date_seance', '>=', now()->startOfDay())
            ->orderBy('date_seance', 'asc')
            ->get();

        $montantEstime = $membres->count() * $cycle->montant_part;

        $fileAttente = [];
        $indexSeance = 0;

        foreach ($membres as $membre) {
            $datePrevue = null;
            $seance = null;

            if (isset($seancesFutures[$indexSeance])) {
                $seance = $seancesFutures[$indexSeance];
                $datePrevue = $seance->date_seance;
                $indexSeance++;
            }

            $fileAttente[] = [
                'membre' => $membre,
                'rang' => $membre->pivot->rang,
                'date_prevue' => $datePrevue ? $datePrevue->format('Y-m-d') : null,
                'est_moi' => ($membre->id === Auth::id()),
                'montant_estime' => $montantEstime,
                'seance' => $seance,
            ];
        }

        return response()->json([
            'succes' => true,
            'cycle' => $cycle,
            'file_attente' => $fileAttente,
        ]);
    }

    /**
     * Marquer un membre comme ayant perçu son lot (rotation vers la fin).
     */
    public function marquerPaye(Request $request, $cycleId, $userId): JsonResponse
    {
        $cycle = Cycle::findOrFail($cycleId);
        $maxRang = $cycle->membres()->max('rang') ?? 0;

        $cycle->membres()->updateExistingPivot($userId, [
            'rang' => $maxRang + 1,
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Membre marqué comme payé et déplacé à la fin de la file.',
        ]);
    }
}
