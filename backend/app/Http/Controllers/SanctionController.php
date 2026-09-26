<?php

namespace App\Http\Controllers;

use App\Models\Sanction;
use App\Models\User;
use App\Models\Seance;
use App\Http\Requests\StoreSanctionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SanctionController extends Controller
{
    public function index()
    {
        // On liste les sanctions, les impayées en premier
        $sanctions = Sanction::with('user')
            ->orderBy('est_reglee', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('sanctions.index', compact('sanctions'));
    }

    public function create()
    {
        $membres = User::where('status', 'actif')->orderBy('name')->get();
        return view('sanctions.create', compact('membres'));
    }

    public function store(StoreSanctionRequest $request)
    {
        // Par défaut, une sanction est créée comme "Non réglée"
        Sanction::create([
            'user_id' => $request->user_id,
            'montant' => $request->montant,
            'motif' => $request->motif,
            'est_reglee' => false
        ]);

        return redirect()->route('sanctions.index')
            ->with('success', 'Sanction enregistrée. Le membre doit la payer.');
    }

    /**
     * Action pour encaisser l'argent de l'amende
     */
    public function markAsPaid(Sanction $sanction)
    {
        if ($sanction->est_reglee) {
            return back()->with('error', 'Cette sanction est déjà payée.');
        }

        // On cherche la séance active pour y mettre l'argent
        $seance = Seance::where('statut', 'ouverte')->latest('date_seance')->first();

        if (!$seance) {
            return back()->with('error', 'Impossible d\'encaisser : Aucune séance n\'est ouverte.');
        }

        // Transaction atomique
        DB::transaction(function () use ($sanction, $seance) {

            // 1. Marquer comme payée
            $sanction->update([
                'est_reglee' => true,
                'seance_id' => $seance->id // On note lors de quelle séance ça a été payé
            ]);

            // 2. Ajouter l'argent à la caisse
            $seance->total_encaisse += $sanction->montant;
            $seance->save();
        });

        return back()->with('success', 'Amende encaissée ! Le solde de la séance a été mis à jour.');
    }

    public function destroy(Sanction $sanction)
    {
        if ($sanction->est_reglee) {
            return back()->with('error', 'Impossible de supprimer une sanction déjà encaissée (problème comptable).');
        }

        $sanction->delete();
        return back()->with('success', 'Sanction annulée.');
    }
}
