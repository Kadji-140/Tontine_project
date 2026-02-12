<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaiementLotController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'seance_id' => 'required|exists:seances,id',
            'user_id' => 'required|exists:users,id',
            'montant' => 'required|numeric|min:0',
        ]);

        $seance = \App\Models\Seance::findOrFail($request->seance_id);

        // Calculer le total encaissé pour la TONTINE sur TOUT le cycle
        $totalTontineCycle = \App\Models\Cotisation::whereHas('seance', function($q) use ($seance) {
            $q->where('cycle_id', $seance->cycle_id);
        })->where('type', 'tontine')->sum('montant');

        // Calculer ce qui a déjà été versé (sorti) pour la TONTINE sur ce cycle
        $totalPaiementsCycle = \App\Models\PaiementLot::where('cycle_id', $seance->cycle_id)
            ->where('statut', '!=', 'rejete')
            ->sum('montant');

        $disponibleTontine = $totalTontineCycle - $totalPaiementsCycle;

        // Vérifier si le montant tontine global disponible est suffisant
        if ($disponibleTontine < $request->montant) {
            return redirect()->back()->with('error', "Fonds TONTINE globaux insuffisants pour ce cycle. Disponible: " . number_format($disponibleTontine) . " F (Total collecté: " . number_format($totalTontineCycle) . " F, Déjà versé: " . number_format($totalPaiementsCycle) . " F). Impossible de verser " . number_format($request->montant) . " F.");
        }

        // --- NOUVELLE VÉRIFICATION DE DATE SELON FRÉQUENCE ---
        $lastPayment = \App\Models\PaiementLot::where('cycle_id', $seance->cycle_id)
            ->where('statut', '!=', 'rejete') // On ignore les rejetés, mais en attente/confirme comptent
            ->latest('date_paiement')
            ->first();

        if ($lastPayment) {
            $freq = $seance->cycle->frequence_paiement ?? 'mensuelle'; // mensuelle ou hebdomadaire
            $lastDate = \Illuminate\Support\Carbon::parse($lastPayment->date_paiement);
            
            if ($freq === 'hebdomadaire') {
                $nextAllowedDate = $lastDate->copy()->addWeek();
            } else {
                // Par défaut mensuelle
                $nextAllowedDate = $lastDate->copy()->addMonth();
            }
            
            // On compare avec la date actuelle (moment du clic)
            if (now()->lt($nextAllowedDate)) {
                 return redirect()->back()->with('error', "Impossible d'effectuer un nouveau versement maintenant. " . 
                    "Le dernier paiement (" . $lastDate->format('d/m/Y') . ") impose une attente jusqu'au " . $nextAllowedDate->format('d/m/Y') . ".");
            }
        }
        // --------------------------------------------------

        return \Illuminate\Support\Facades\DB::transaction(function() use ($request, $seance) {
            // 1. Enregistrer le paiement
            $paiement = \App\Models\PaiementLot::create([
                'cycle_id' => $seance->cycle_id,
                'user_id' => $request->user_id,
                'seance_id' => $seance->id,
                'montant' => $request->montant,
                'date_paiement' => now(),
                'statut' => 'en_attente' // ou 'confirme' directement si c'est la caissiere qui le fait ? On va dire en attente confirmation membre.
            ]);

            // 2. Note: On ne débite PAS la caisse de la séance car l'argent vient du pot global Tontine
            // Le paiement est enregistré dans PaiementLot, ce qui suffit pour la comptabilité globale



            // 3. Envoyer une annonce (Notification publique pour le moment car pas de message privé)
            \App\Models\Annonce::create([
                'titre' => '💰 Gain de Tontine perçu par ' . \App\Models\User::find($request->user_id)->name . ' !',
                'message' => "Le membre " . \App\Models\User::find($request->user_id)->name . " a reçu son gain de tontine d'un montant de " . number_format($request->montant) . " FCFA lors de la séance du " . $seance->date_seance->format('d/m/Y') . ".",
                'user_id' => auth()->id(), // L'auteur est l'admin/trésorier connecté
                'target_role' => null // Visible par tous (public)
            ]);


            return redirect()->back()->with('success', 'Le versement a été autorisé et débité de la caisse. Une annonce a été publiée.');
        });
    }

    public function confirmer(\App\Models\PaiementLot $paiement)
    {
        // Seul le destinataire peut confirmer
        if (auth()->id() !== $paiement->user_id) {
            abort(403, 'Action non autorisée.');
        }

        $paiement->update(['statut' => 'confirme']);

        return back()->with('success', 'Versement confirmé. Merci !');
    }

    /**
     * Historique des gains pour l'utilisateur connecté
     */
    public function historique()
    {
        $gains = \App\Models\PaiementLot::where('user_id', auth()->id())
            ->with(['cycle', 'seance'])
            ->orderBy('date_paiement', 'desc')
            ->get();

        return view('membres.gains', compact('gains'));
    }
}
