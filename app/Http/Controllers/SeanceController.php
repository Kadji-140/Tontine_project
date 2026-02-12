<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use App\Models\Cycle;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\StoreSeanceRequest;
use App\Http\Requests\UpdateSeanceRequest;

class SeanceController extends Controller
{
    public function index()
    {
        // On récupère les séances triées par date (la plus récente en haut)
        // 'with' permet d'optimiser la requête pour charger le nom du Cycle associé
        $seances = Seance::with('cycle')->orderBy('date_seance', 'desc')->get();
        return view('seances.index', compact('seances'));
    }

    public function create()
    {
        // On a besoin de la liste des cycles ACTIFS pour le menu déroulant
        $cycles = Cycle::where('est_actif', true)->get();
        return view('seances.create', compact('cycles'));
    }

    public function store(StoreSeanceRequest $request)
    {
        Seance::create($request->validated());

        return redirect()->route('seances.index')
            ->with('success', 'Séance planifiée avec succès.');
    }

    /**
     * C'est la page la plus importante de l'application !
     * C'est ici que le trésorier va gérer la réunion.
     */
    public function show(Seance $seance)
    {
        // VÉRIFICATION: Si la date de la séance est passée et que ce n'est pas un admin
        if (auth()->user()->role !== 'admin' && now()->startOfDay()->gt($seance->date_seance)) {
            // Fermer automatiquement la séance si elle est encore ouverte
            if ($seance->statut === 'ouverte') {
                $seance->update(['statut' => 'fermee']);
            }
            
            return redirect()->route('seances.index')
                ->with('error', 'Cette séance est fermée. La date de la séance (' . $seance->date_seance->format('d/m/Y') . ') est déjà passée.');
        }

        // Fermer automatiquement la séance si la date est passée (même pour admin, mais on lui permet de voir)
        if (now()->startOfDay()->gt($seance->date_seance) && $seance->statut === 'ouverte') {
            $seance->update(['statut' => 'fermee']);
        }

        // 1. On charge la séance avec ses cotisations et le membre associé à chaque cotisation
        $seance->load(['cotisations.user', 'cycle']);

        // Calcul du bénéficiaire attendu
        $beneficiaireAttendu = null;
        if ($seance->cycle) {
            $dateDebut = $seance->cycle->date_debut;
            $freq = $seance->cycle->frequence_paiement ?? 'mensuelle';
            
            // Calcul du rang théorique
            if ($freq === 'hebdomadaire') {
                $rang = $dateDebut->diffInWeeks($seance->date_seance) + 1;
            } else {
                // Par défaut mensuelle
                $rang = $dateDebut->diffInMonths($seance->date_seance) + 1;
            }
            
            // Trouver le membre avec ce rang
            // Attention: on doit charger les membres avec pivot pour chercher
            $beneficiaireAttendu = $seance->cycle->membres()->wherePivot('rang', (int)$rang)->first();
        }

        // 2. On récupère la liste de tous les membres actifs pour le formulaire d'ajout
        $membres = User::where('status', 'actif')->orderBy('name')->get();

        // 3. Calculer les cotisations déjà versées pour la TONTINE par membre pour cette séance
        $cotisationsTontineParMembre = $seance->cotisations()
            ->where('type', 'tontine')
            ->selectRaw('user_id, SUM(montant) as total_paye')
            ->groupBy('user_id')
            ->pluck('total_paye', 'user_id');

        // 4. Vérifier si un versement récent existe pour ce cycle (pour bloquer le formulaire)
        $dernierPaiement = \App\Models\PaiementLot::where('cycle_id', $seance->cycle_id)
            ->where('statut', '!=', 'rejete')
            ->latest('date_paiement')
            ->first();
        
        $paiementRecentBloque = false;
        $prochainPaiementDate = null;
        
        if ($dernierPaiement) {
            $freq = $seance->cycle->frequence_paiement ?? 'mensuelle';
            $lastDate = \Illuminate\Support\Carbon::parse($dernierPaiement->date_paiement);
            
            if ($freq === 'hebdomadaire') {
                $nextAllowedDate = $lastDate->copy()->addWeek();
            } else {
                $nextAllowedDate = $lastDate->copy()->addMonth();
            }
            
            if (now()->lt($nextAllowedDate)) {
                $paiementRecentBloque = true;
                $prochainPaiementDate = $nextAllowedDate;
            }
        }

        // 5. On envoie tout à la vue
        return view('seances.show', compact('seance', 'membres', 'cotisationsTontineParMembre', 'beneficiaireAttendu', 'paiementRecentBloque', 'prochainPaiementDate', 'dernierPaiement'));
    }

    public function edit(Seance $seance)
    {
        $cycles = Cycle::all(); // Pour l'édition, on permet de changer même vers un cycle inactif si erreur
        return view('seances.edit', compact('seance', 'cycles'));
    }

    public function update(UpdateSeanceRequest $request, Seance $seance)
    {
        $seance->update($request->validated());

        return redirect()->route('seances.index')
            ->with('success', 'Séance mise à jour.');
    }

    public function destroy(Seance $seance)
    {
        // Sécurité : Impossible de supprimer une séance s'il y a déjà de l'argent enregistré
        if ($seance->cotisations()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer : des cotisations sont liées à cette séance.');
        }

        $seance->delete();
        return redirect()->route('seances.index')->with('success', 'Séance supprimée.');
    }

    /**
     * Génère le PDF de la séance
     */
    public function downloadReport(Seance $seance)
    {
        // 1. On charge toutes les données liées à la séance
        // 1. On charge toutes les données liées à la séance
        $seance->load([
            'cycle',
            'cotisations.user',
            'prets.user',
            'sanctions.user',
            'remboursements.user', // Nouveau
            'fonds_depenses.user'  // Nouveau
        ]);

        // 3. Calculs pour le résumé
        $totalCotisations = $seance->cotisations->sum('montant');
        $totalRemboursements = $seance->remboursements->sum('montant');
        $totalFonds = $seance->fonds_depenses->sum('montant');
        
        $totalEntrees = $totalCotisations + $totalRemboursements + $totalFonds;
        
        $totalSortiesPrets = $seance->prets->where('statut', 'valide')->sum('montant_demande');
        $totalDepenses = $seance->depenses->where('statut', 'validee')->sum('montant');
        
        $totalSorties = $totalSortiesPrets + $totalDepenses;

        // Si tu as géré les amendes encaissées
        // $totalAmendes = ...

        // 4. Génération du PDF
        // 4. Génération du PDF
        $pdf = Pdf::loadView('reports.seance_pdf', compact(
            'seance', 
            'totalCotisations', 
            'totalRemboursements', 
            'totalFonds',
            'totalEntrees',
            'totalSortiesPrets',
            'totalDepenses',
            'totalSorties'
        ));

        // 5. Téléchargement (Nom du fichier : Rapport_Seance_2026-01-20.pdf)
        // stream a la place de doswload permet de previsualiser
        return $pdf->stream('Rapport_Seance_' . $seance->date_seance->format('Y-m-d') . '.pdf');
    }

    /**
     * Upload de la preuve de versement (Trésorier/Admin)
     */
    public function uploadPreuve(\Illuminate\Http\Request $request, Seance $seance)
    {
        $request->validate([
            'preuve' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096', // Max 4MB
        ]);

        // Suppression de l'ancienne preuve si elle existe
        if ($seance->preuve_versement) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($seance->preuve_versement);
        }

        $path = $request->file('preuve')->store('preuves_versement', 'public');

        $seance->update([
            'preuve_versement' => $path,
            'etat_versement' => 'en_attente'
        ]);

        // Créer une notification (Annonce) pour les admins
        \App\Models\Annonce::create([
            'titre' => '💰 Nouveau versement en attente',
            'message' => "Un reçu de paiement a été téléversé pour la séance du {$seance->date_seance->format('d/m/Y')}. \nEn attente de validation.",
            'user_id' => auth()->id(),
            'target_role' => 'admin'
        ]);

        return back()->with('success', 'Preuve de versement envoyée avec succès.');
    }

    /**
     * Valider le versement (Admin seulement)
     */
    public function validerPreuve(Seance $seance)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Action réservée aux administrateurs.');
        }

        $seance->update(['etat_versement' => 'valide']);

        return back()->with('success', 'Versement validé avec succès.');
    }

    /**
     * Rejeter le versement (Admin seulement)
     */
    public function rejeterPreuve(Seance $seance)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Action réservée aux administrateurs.');
        }

        // On ne supprime pas forcément le fichier, on marque juste comme rejeté pour historique
        $seance->update(['etat_versement' => 'rejete']);

        return back()->with('error', 'Versement rejeté.');
    }
}
