<?php

namespace App\Http\Controllers;

use App\Models\Remboursement;
use App\Models\Pret;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RemboursementController extends Controller
{
    /**
     * Recherche les dettes d'un membre (Ajax)
     */
    public function searchDettes(Request $request, Seance $seance)
    {
        $search = $request->query('q');

        // Trouver les membres qui ont des prêts validés ET non soldés
        $users = User::whereHas('prets', function($q) use ($seance) {
            $q->whereIn('statut', ['valide']) // Prêts en cours
              ->where('interet_total', '>', 0); // Simplification: on vérifie via le reste à payer si possible, mais ici on prend tous les validés
        })
        ->where('name', 'LIKE', "%{$search}%")
        ->get();

        // Pour chaque user, calculer le reste à payer
        $results = [];
        foreach($users as $user) {
            // Calculer la dette totale
            $prets = Pret::where('user_id', $user->id)->where('statut', 'valide')->get();
            $totalDette = 0;
            $details = [];

            foreach($prets as $pret) {
                $montantDu = $pret->montant_demande + $pret->interet_total;
                $dejaRembourse = Remboursement::where('pret_id', $pret->id)->sum('montant');
                $resteList = $montantDu - $dejaRembourse;

                if ($resteList > 0) {
                    $totalDette += $resteList;
                    $details[] = [
                        'pret_id' => $pret->id,
                        'date' => $pret->created_at->format('d/m/Y'),
                        'montant_du' => $montantDu,
                        'reste' => $resteList
                    ];
                }
            }

            if ($totalDette > 0) {
                $results[] = [
                    'id' => $user->id,
                    'text' => $user->name . ' (Dette : ' . number_format($totalDette) . ' F)',
                    'total_dette' => $totalDette,
                    'prets' => $details
                ];
            }
        }

        return response()->json($results);
    }

    /**
     * Enregistrer un remboursement
     */
    public function store(Request $request)
    {
        $request->validate([
            'seance_id' => 'required|exists:seances,id',
            'user_id' => 'required|exists:users,id',
            'montant' => 'required|numeric|min:500',
            'pret_id' => 'required|exists:prets,id' // On rembourse un prêt spécifique
        ]);

        return DB::transaction(function () use ($request) {
            $seance = Seance::findOrFail($request->seance_id);
            $pret = Pret::findOrFail($request->pret_id);

            // Vérifier que c'est bien le prêt du user
            if($pret->user_id != $request->user_id) {
                abort(403, "Ce prêt n'appartient pas à cet utilisateur.");
            }

            // Calcul du reste à payer
            $montantDu = $pret->montant_demande + $pret->interet_total;
            $dejaRembourse = Remboursement::where('pret_id', $pret->id)->sum('montant');
            $reste = $montantDu - $dejaRembourse;

            if ($request->montant > $reste) {
                throw new \Exception("Le montant (" . number_format($request->montant) . ") dépasse le reste à payer (" . number_format($reste) . ").");
            }

            // Enregistrer le remboursement
            Remboursement::create([
                'seance_id' => $seance->id,
                'pret_id' => $pret->id,
                'user_id' => $request->user_id,
                'enregistre_par' => Auth::id(),
                'montant' => $request->montant
            ]);

            // Mettre à jour la caisse de la séance
            $seance->total_encaisse += $request->montant;
            $seance->save();

            // Si tout est remboursé, marquer le prêt comme 'rembourse'
            if ($request->montant == $reste) { // Ou >= mais on a checké avant
                $pret->statut = 'rembourse';
                $pret->save();
            }

            return back()->with('success', 'Remboursement de ' . number_format($request->montant) . ' FCFA enregistré.');
        });
    }
}
