<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentQueueController extends Controller
{
    /**
     * Affiche l'ordre de passage (Queue de paiement)
     */
    public function index(Cycle $cycle)
    {
        // 1. Récupérer les membres dans l'ordre du rang (Queue actuelle)
        $membres = $cycle->membres; // La relation est déjà orderByPivot('rang', 'asc')

        // 2. Récupérer les prochaines séances (Dates de paiement futures)
        // On inclut la séance d'aujourd'hui si elle est ouverte
        $futureSeances = $cycle->seances()
            ->where('date_seance', '>=', now()->startOfDay())
            ->orderBy('date_seance', 'asc')
            ->get();

        // 3. Préparer les données pour la vue
        $queue = [];
        $seanceIndex = 0;
        
        // Estimation du montant (Somme des cotisations = Nombre de membres * Montant part)
        // Ou plus précis : Somme des cotisations réelles si on pouvait le prédire, mais ici on estime.
        // Le user a dit "Somme de toutes les cotisations du mois". 
        // Si tout le monde cotise : 
        $montantEstime = $cycle->membres()->count() * $cycle->montant_part;

        foreach ($membres as $membre) {
            $datePrevue = null;
            $seance = null;
            
            if (isset($futureSeances[$seanceIndex])) {
                $seance = $futureSeances[$seanceIndex];
                $datePrevue = $seance->date_seance;
                $seanceIndex++;
            }

            $queue[] = (object) [
                'membre' => $membre,
                'rang' => $membre->pivot->rang,
                'date_prevue' => $datePrevue,
                'is_current_user' => $membre->id === Auth::id(),
                'montant_estime' => $montantEstime,
                'seance' => $seance
            ];
        }

        return view('cycles.payment_queue', compact('cycle', 'queue'));
    }

    /**
     * Marquer un membre comme ayant reçu sa part (Rotation)
     */
    public function markAsPaid(Request $request, Cycle $cycle, $userId)
    {
        // 1. Vérifier si l'utilisateur est admin ou trésorier
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'tresorier') {
            return back()->with('error', 'Action non autorisée.');
        }

        // 2. Trouver le rang maximum actuel
        $maxRang = $cycle->membres()->max('rang');

        // 3. Mettre à jour le rang du membre pour le mettre à la fin
        $cycle->membres()->updateExistingPivot($userId, [
            'rang' => $maxRang + 1
        ]);

        return back()->with('success', 'Membre marqué comme payé et déplacé à la fin de la file.');
    }
}
