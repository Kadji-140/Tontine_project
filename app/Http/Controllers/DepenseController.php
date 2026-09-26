<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DepenseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'seance_id' => 'required|exists:seances,id',
            'motif' => 'required|string|max:255',
            'montant' => 'required|numeric|min:100', // Pas de dépense négative
        ]);

        return DB::transaction(function () use ($request) {
            $seance = Seance::findOrFail($request->seance_id);

            // 1. Vérifier s'il y a assez d'argent (Fonds totaux du cycle)
            $totalFonds = \App\Models\FondsDepense::where('cycle_id', $seance->cycle_id)->sum('montant');
            $totalDepenses = \App\Models\Depense::whereHas('seance', fn($q) => $q->where('cycle_id', $seance->cycle_id))
                ->where('statut', 'validee')->sum('montant');
            
            $fondsDisponibles = $totalFonds - $totalDepenses;

            // Note: On peut aussi autoriser le "déficit" temporaire sur la caisse physique si on veut, 
            // mais logiquement on ne peut pas dépenser ce qu'on n'a pas. 
            // Ici on vérifie la CAISSE PHYSIQUE de la séance ou le BUDGET global ?
            // Le user dit: "la depense s'effectue sur La caisse de la tontine"
            
            // Si on parle de sortir l'argent PHYSIQUEMENT de la séance en cours :
            if ($seance->total_encaisse < $request->montant) {
                 // C'est bloquant physiquement. On ne peut pas donner ce qu'on n'a pas sur la table.
                 // MAIS le user dit "la dépense ne peut pas être supérieure à la somme qui a été cotisée... or en réalité la dépense s'effectue sur La caisse"
                 // Ça veut dire qu'il veut autoriser même si total_encaisse < montant ? 
                 // NON, si je n'ai pas d'argent en main, je ne peux pas payer.
                 // PEUT-ETRE qu'il veut dire qu'on prend dans le "Fonds" accumulé ?
                 // Si l'argent est à la banque, on ne peut pas le sortir cash en séance sans retrait précèdant.
                 
                 // Interprétation: Il veut qu'on vérifie si on a le BUDGET (Fonds), mais techniquement si la caisse de la séance est vide, on ne peut pas payer cash.
                 // Sauf si le "Solde Caisse" global (billets reportés) est géré.
                 // Dans notre système actuel, 'total_encaisse' est ce qui est entré CE JOUR LÀ + ce qui reste ? 
                 // Non, total_encaisse est juste la somme des cotisations DU JOUR.
                 
                 // Correction: On doit vérifier le SOLDE GLOBAL DE LA CAISSE (Physique).
                 // Solde Global = (Total Entrées - Total Sorties)
                 $soldeGlobal = (
                    \App\Models\Cotisation::sum('montant') + 
                    \App\Models\Remboursement::sum('montant') + 
                    \App\Models\FondsDepense::sum('montant')
                 ) - (
                    \App\Models\Pret::where('statut', 'valide')->sum('montant_demande') + 
                    \App\Models\Depense::where('statut', 'validee')->sum('montant')
                 );

                 if ($soldeGlobal < $request->montant) {
                    return back()->with('error', 'Fonds insuffisants en Caisse Globale (' . number_format($soldeGlobal) . ' F).');
                 }
            }

            // 2. Créer la dépense (En attente par défaut)
            Depense::create([
                'seance_id' => $request->seance_id,
                'enregistre_par' => Auth::id(),
                'motif' => $request->motif,
                'montant' => $request->montant,
                'statut' => 'en_attente'
            ]);

            // NOTE: On ne débite PAS la caisse tout de suite. Le président doit valider.

            return back()->with('success', 'Dépense enregistrée et mise en attente de validation.');
        });
    }

    public function valider(Depense $depense)
    {
        // Sécurité : Seul l'ADMIN (Président) peut valider
        if(auth()->user()->role !== 'admin') {
            abort(403, 'Accès réservé au Président (Admin).');
        }

        if($depense->statut === 'validee') {
             return back()->with('warning', 'Cette dépense est déjà validée.');
        }

        return DB::transaction(function() use ($depense) {
            $seance = $depense->seance;

            // On vérifie le solde maintenant
            if ($seance->total_encaisse < $depense->montant) {
                return back()->with('error', 'Impossible de valider : Fonds insuffisants.');
            }

            // Débiter la caisse
            $seance->total_encaisse -= $depense->montant;
            $seance->save();

            // Mettre à jour le statut
            $depense->update(['statut' => 'validee']);

            return back()->with('success', 'Dépense validée et montant débité.');
        });
    }

    public function rejeter(Depense $depense)
    {
        if(auth()->user()->role !== 'admin') {
            abort(403, 'Accès réservé au Président (Admin).');
        }

        if($depense->statut === 'validee') {
            return back()->with('error', 'Impossible de rejetter une dépense déjà validée (annulez-la plutôt).');
        }

        $depense->update(['statut' => 'rejetee']);
        return back()->with('success', 'Dépense rejetée.');
    }

    public function destroy(Depense $depense)
    {
        return DB::transaction(function () use ($depense) {
            $seance = $depense->seance;

            // 1. Remettre l'argent dans la caisse UNIQUEMENT si la dépense avait été validée (et donc débitée)
            if ($depense->statut === 'validee') {
                $seance->total_encaisse += $depense->montant;
                $seance->save();
                $message = 'Dépense annulée, montant réintégré à la caisse.';
            } else {
                $message = 'Dépense en attente supprimée.';
            }

            // 2. Supprimer la ligne
            $depense->delete();

            return back()->with('success', $message);
        });
    }
}
