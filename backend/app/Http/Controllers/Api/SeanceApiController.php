<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Cycle;
use App\Models\PaiementLot;
use App\Models\Seance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SeanceApiController extends Controller
{
    /**
     * Liste des séances avec information sur le cycle.
     */
    public function index(): JsonResponse
    {
        $seances = Seance::with('cycle')->orderBy('date_seance', 'desc')->get();

        return response()->json([
            'succes' => true,
            'seances' => $seances,
        ]);
    }

    /**
     * Création d'une nouvelle séance.
     */
    public function store(Request $request): JsonResponse
    {
        $valide = $request->validate([
            'cycle_id' => ['required', 'exists:cycles,id'],
            'date_seance' => ['required', 'date'],
            'statut' => ['nullable', 'string', 'in:ouverte,fermee'],
        ]);

        $seance = Seance::create([
            'cycle_id' => $valide['cycle_id'],
            'date_seance' => $valide['date_seance'],
            'statut' => $valide['statut'] ?? 'ouverte',
            'total_encaisse' => 0,
            'etat_versement' => 'non_verse',
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Séance planifiée avec succès.',
            'seance' => $seance->load('cycle'),
        ], 201);
    }

    /**
     * Détails complets d'une séance (cotisations, prêts, remboursements, sanctions, bénéficiaire attendu).
     */
    public function show($id): JsonResponse
    {
        $seance = Seance::with([
            'cycle.membres',
            'cotisations.user',
            'prets.user',
            'remboursements.user',
            'sanctions.user',
            'depenses',
            'fonds_depenses.user',
        ])->findOrFail($id);

        $utilisateur = Auth::user();

        // Clôture automatique si la date est échue
        if (now()->startOfDay()->gt($seance->date_seance) && $seance->statut === 'ouverte') {
            $seance->update(['statut' => 'fermee']);
        }

        // Calcul du bénéficiaire attendu selon la fréquence
        $beneficiaireAttendu = null;
        if ($seance->cycle) {
            $dateDebut = Carbon::parse($seance->cycle->date_debut);
            $freq = $seance->cycle->frequence_paiement ?? 'mensuelle';

            if ($freq === 'hebdomadaire') {
                $rang = $dateDebut->diffInWeeks($seance->date_seance) + 1;
            } else {
                $rang = $dateDebut->diffInMonths($seance->date_seance) + 1;
            }

            $beneficiaireAttendu = $seance->cycle->membres()->wherePivot('rang', (int)$rang)->first();
        }

        // Membres actifs pour sélection
        $membresActifs = User::where('status', 'actif')->orderBy('name')->get();

        // Cotisations déjà versées par membre pour la tontine
        $cotisationsTontineParMembre = $seance->cotisations
            ->where('type', 'tontine')
            ->groupBy('user_id')
            ->map(fn($c) => $c->sum('montant'));

        // Vérification de blocage de paiement de lot récent
        $dernierPaiement = PaiementLot::where('cycle_id', $seance->cycle_id)
            ->where('statut', '!=', 'rejete')
            ->latest('date_paiement')
            ->first();

        $paiementRecentBloque = false;
        $prochainPaiementDate = null;

        if ($dernierPaiement) {
            $freq = $seance->cycle->frequence_paiement ?? 'mensuelle';
            $derniereDate = Carbon::parse($dernierPaiement->date_paiement);

            $prochaineDateAutorisee = ($freq === 'hebdomadaire')
                ? $derniereDate->copy()->addWeek()
                : $derniereDate->copy()->addMonth();

            if (now()->lt($prochaineDateAutorisee)) {
                $paiementRecentBloque = true;
                $prochainPaiementDate = $prochaineDateAutorisee->format('Y-m-d');
            }
        }

        return response()->json([
            'succes' => true,
            'seance' => $seance,
            'beneficiaire_attendu' => $beneficiaireAttendu,
            'membres_actifs' => $membresActifs,
            'cotisations_tontine_par_membre' => $cotisationsTontineParMembre,
            'paiement_recent_bloque' => $paiementRecentBloque,
            'prochain_paiement_date' => $prochainPaiementDate,
            'dernier_paiement' => $dernierPaiement,
        ]);
    }

    /**
     * Mise à jour d'une séance (date, statut).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $seance = Seance::findOrFail($id);

        $valide = $request->validate([
            'date_seance' => ['sometimes', 'date'],
            'statut' => ['sometimes', 'string', 'in:ouverte,fermee'],
        ]);

        $seance->update($valide);

        return response()->json([
            'succes' => true,
            'message' => 'Séance mise à jour avec succès.',
            'seance' => $seance->fresh(['cycle']),
        ]);
    }

    /**
     * Suppression d'une séance.
     */
    public function destroy($id): JsonResponse
    {
        $seance = Seance::findOrFail($id);
        $seance->delete();

        return response()->json([
            'succes' => true,
            'message' => 'Séance supprimée avec succès.',
        ]);
    }

    /**
     * Téléversement du justificatif de versement bancaire.
     */
    public function uploaderPreuve(Request $request, $id): JsonResponse
    {
        $seance = Seance::findOrFail($id);

        $request->validate([
            'preuve' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        if ($seance->preuve_versement) {
            Storage::disk('public')->delete($seance->preuve_versement);
        }

        $chemin = $request->file('preuve')->store('preuves_versement', 'public');

        $seance->update([
            'preuve_versement' => $chemin,
            'etat_versement' => 'en_attente',
        ]);

        Annonce::create([
            'titre' => '💰 Nouveau versement en attente de vérification',
            'message' => "Un reçu de paiement a été téléversé pour la séance du {$seance->date_seance->format('d/m/Y')}.",
            'user_id' => Auth::id(),
            'target_role' => 'admin',
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Justificatif de versement téléversé avec succès.',
            'preuve_url' => Storage::disk('public')->url($chemin),
            'etat_versement' => 'en_attente',
        ]);
    }

    /**
     * Validation du versement par un administrateur.
     */
    public function validerPreuve($id): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['succes' => false, 'message' => 'Action réservée aux administrateurs.'], 403);
        }

        $seance = Seance::findOrFail($id);
        $seance->update(['etat_versement' => 'valide']);

        return response()->json([
            'succes' => true,
            'message' => 'Versement validé avec succès.',
            'etat_versement' => 'valide',
        ]);
    }

    /**
     * Rejet du versement.
     */
    public function rejeterPreuve(Request $request, $id): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['succes' => false, 'message' => 'Action réservée aux administrateurs.'], 403);
        }

        $seance = Seance::findOrFail($id);
        $seance->update(['etat_versement' => 'rejete']);

        return response()->json([
            'succes' => true,
            'message' => 'Versement marqué comme rejeté.',
            'etat_versement' => 'rejete',
        ]);
    }

    /**
     * Téléchargement du rapport de séance PDF.
     */
    public function telechargerRapport($id)
    {
        $seance = Seance::with([
            'cycle',
            'cotisations.user',
            'prets.user',
            'sanctions.user',
            'remboursements.user',
            'fonds_depenses.user',
            'depenses',
        ])->findOrFail($id);

        $totalCotisations = $seance->cotisations->sum('montant');
        $totalRemboursements = $seance->remboursements->sum('montant');
        $totalFonds = $seance->fonds_depenses->sum('montant');
        $totalEntrees = $totalCotisations + $totalRemboursements + $totalFonds;

        $totalSortiesPrets = $seance->prets->where('statut', 'valide')->sum('montant_demande');
        $totalDepenses = $seance->depenses->where('statut', 'validee')->sum('montant');
        $totalSorties = $totalSortiesPrets + $totalDepenses;

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

        return $pdf->download('Rapport_Seance_' . $seance->date_seance->format('Y-m-d') . '.pdf');
    }
}
