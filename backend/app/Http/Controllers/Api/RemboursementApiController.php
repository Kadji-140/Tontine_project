<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pret;
use App\Models\Remboursement;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RemboursementApiController extends Controller
{
    /**
     * Recherche des membres ayant une dette active pour remboursement.
     */
    public function rechercherDettes(Request $request, $seanceId): JsonResponse
    {
        $search = $request->query('q', '');

        $membres = User::pourTenantActuel()
            ->whereHas('prets', function ($q) {
                $q->where('statut', 'valide');
            })
            ->when($search, fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
            ->get();

        $resultats = [];
        foreach ($membres as $membre) {
            $prets = Pret::where('user_id', $membre->id)->where('statut', 'valide')->get();
            $totalDette = 0;
            $details = [];

            foreach ($prets as $pret) {
                $montantDu = $pret->montant_demande + $pret->interet_total;
                $dejaRembourse = Remboursement::where('pret_id', $pret->id)->sum('montant');
                $reste = $montantDu - $dejaRembourse;

                if ($reste > 0) {
                    $totalDette += $reste;
                    $details[] = [
                        'pret_id' => $pret->id,
                        'date' => $pret->created_at->format('d/m/Y'),
                        'montant_du' => $montantDu,
                        'reste' => $reste,
                        'date_echeance' => $pret->date_echeance ? $pret->date_echeance->format('d/m/Y') : null,
                    ];
                }
            }

            if ($totalDette > 0) {
                $resultats[] = [
                    'id' => $membre->id,
                    'nom' => $membre->name,
                    'total_dette' => $totalDette,
                    'prets' => $details,
                ];
            }
        }

        return response()->json([
            'succes' => true,
            'resultats' => $resultats,
        ]);
    }

    /**
     * Enregistrement d'un remboursement et mise à jour de la caisse.
     */
    public function store(Request $request): JsonResponse
    {
        $valide = $request->validate([
            'seance_id' => ['required', 'exists:seances,id'],
            'user_id' => ['required', 'exists:users,id'],
            'pret_id' => ['required', 'exists:prets,id'],
            'montant' => ['required', 'numeric', 'min:500'],
        ]);

        return DB::transaction(function () use ($valide) {
            $seance = Seance::findOrFail($valide['seance_id']);
            $pret = Pret::findOrFail($valide['pret_id']);

            if ($pret->user_id != $valide['user_id']) {
                return response()->json([
                    'succes' => false,
                    'message' => "Ce prêt n'appartient pas à ce membre.",
                ], 403);
            }

            $montantDu = $pret->montant_demande + $pret->interet_total;
            $dejaRembourse = Remboursement::where('pret_id', $pret->id)->sum('montant');
            $reste = $montantDu - $dejaRembourse;

            if ($valide['montant'] > $reste) {
                return response()->json([
                    'succes' => false,
                    'message' => "Le montant versé (" . number_format($valide['montant']) . " F) dépasse le solde restant (" . number_format($reste) . " F).",
                ], 422);
            }

            $remboursement = Remboursement::create([
                'seance_id' => $seance->id,
                'pret_id' => $pret->id,
                'user_id' => $valide['user_id'],
                'montant' => $valide['montant'],
                'enregistre_par' => Auth::id(),
            ]);

            $seance->total_encaisse += $valide['montant'];
            $seance->save();

            $solde = false;
            if ($valide['montant'] == $reste) {
                $pret->update(['statut' => 'rembourse']);
                $solde = true;
            }

            return response()->json([
                'succes' => true,
                'message' => 'Remboursement de ' . number_format($valide['montant']) . ' F enregistré.',
                'remboursement' => $remboursement,
                'pret_solde' => $solde,
                'total_encaisse_seance' => $seance->total_encaisse,
            ], 201);
        });
    }
}
