<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Pret;
use App\Models\Seance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PretApiController extends Controller
{
    /**
     * Liste des prêts avec filtres et statistiques.
     */
    public function index(Request $request): JsonResponse
    {
        $utilisateur = Auth::user();
        $estMembre = ($utilisateur->role === 'membre');

        $query = Pret::with(['user', 'seance.cycle']);

        if ($estMembre) {
            $query->where('user_id', $utilisateur->id);
        } elseif ($request->filled('membre_id')) {
            $query->where('user_id', $request->membre_id);
        }

        if ($request->filled('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        if ($request->query('action_requise') === 'confirmation_date') {
            $query->where('date_echeance_modifiee', true)
                ->where('est_accepte_par_membre', false)
                ->where('statut', 'en_attente');
        }

        $tri = $request->query('tri', 'created_at');
        $ordre = $request->query('ordre', 'desc');
        $prets = $query->orderBy($tri, $ordre)->get();

        $stats = [];
        if (!$estMembre) {
            $stats = [
                'total' => Pret::count(),
                'en_attente' => Pret::where('statut', 'en_attente')->count(),
                'valides' => Pret::where('statut', 'valide')->count(),
                'rembourses' => Pret::where('statut', 'rembourse')->count(),
                'en_retard' => Pret::where('statut', 'valide')->where('date_echeance', '<', now())->count(),
            ];
        }

        return response()->json([
            'succes' => true,
            'prets' => $prets,
            'statistiques' => $stats,
        ]);
    }

    /**
     * Création d'une demande de prêt avec application des limites de date (3 mois ou fin cycle).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'seance_id' => ['required', 'exists:seances,id'],
            'montant_demande' => ['required', 'numeric', 'min:1000'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $utilisateur = Auth::user();
        $idEmprunteur = ($utilisateur->role === 'membre') ? $utilisateur->id : ($request->user_id ?? $utilisateur->id);

        $seance = Seance::with('cycle')->findOrFail($request->seance_id);

        // Restriction de date
        if ($utilisateur->role !== 'admin' && now()->startOfDay()->gt($seance->date_seance)) {
            return response()->json([
                'succes' => false,
                'message' => "Impossible de solliciter un prêt sur une séance déjà échue (" . $seance->date_seance->format('d/m/Y') . ")."
            ], 422);
        }

        $cycle = $seance->cycle;
        $tauxInteret = (float)($cycle->taux_interet ?? 10);
        $interetTotal = $request->montant_demande * ($tauxInteret / 100);

        // Calcul de la date limite autorisée (min entre 3 mois et la fin du cycle)
        $dateDemandee = Carbon::parse($request->date_echeance)->startOfDay();
        $dateLimite3Mois = now()->addMonths(3)->startOfDay();
        $finCycle = Carbon::parse($cycle->date_fin)->startOfDay();
        $dateMaximum = $dateLimite3Mois->min($finCycle);

        $dateAjustee = false;
        if ($dateDemandee->greaterThan($dateMaximum)) {
            $pret = Pret::create([
                'user_id' => $idEmprunteur,
                'seance_id' => $seance->id,
                'montant_demande' => $request->montant_demande,
                'interet_total' => $interetTotal,
                'date_echeance' => $dateMaximum->format('Y-m-d'),
                'date_echeance_modifiee' => true,
                'date_modification_proposee' => $dateMaximum->format('Y-m-d'),
                'est_accepte_par_membre' => false,
                'statut' => 'en_attente',
            ]);

            Annonce::create([
                'titre' => '⚠️ Date ajustée pour votre demande de prêt',
                'message' => "Bonjour. Votre demande de prêt de " . number_format($request->montant_demande) . " F a été ajustée au maximum autorisé : " . $dateMaximum->format('d/m/Y') . ". Merci de confirmer votre accord.",
                'user_id' => $utilisateur->id,
            ]);

            $dateAjustee = true;
        } else {
            $pret = Pret::create([
                'user_id' => $idEmprunteur,
                'seance_id' => $seance->id,
                'montant_demande' => $request->montant_demande,
                'interet_total' => $interetTotal,
                'date_echeance' => $request->date_echeance,
                'est_accepte_par_membre' => true,
                'date_echeance_modifiee' => false,
                'statut' => 'en_attente',
            ]);
        }

        return response()->json([
            'succes' => true,
            'message' => $dateAjustee
                ? 'Demande enregistrée avec échéance ajustée au ' . $dateMaximum->format('d/m/Y') . '. En attente de confirmation par le membre.'
                : 'Demande de prêt enregistrée avec succès.',
            'pret' => $pret->load(['user', 'seance']),
            'date_ajustee' => $dateAjustee,
        ], 201);
    }

    /**
     * Modification / Proposition d'échéance par le bureau.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $pret = Pret::findOrFail($id);

        if ($pret->statut !== 'en_attente') {
            return response()->json(['succes' => false, 'message' => "Ce prêt n'est plus en attente."], 422);
        }

        $request->validate([
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $ancienneDate = Carbon::parse($pret->date_echeance);
        $nouvelleDate = Carbon::parse($request->date_echeance);

        $pret->update([
            'date_echeance_modifiee' => true,
            'date_modification_proposee' => $nouvelleDate->format('Y-m-d'),
            'est_accepte_par_membre' => false,
        ]);

        Annonce::create([
            'titre' => '⚠️ Proposition de nouvelle date pour votre prêt',
            'message' => "Le trésorier propose une nouvelle échéance pour votre prêt (" . $nouvelleDate->format('d/m/Y') . " au lieu de " . $ancienneDate->format('d/m/Y') . ").",
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'succes' => true,
            'message' => "Nouvelle échéance proposée avec succès.",
            'pret' => $pret->fresh(),
        ]);
    }

    /**
     * Acceptation par le membre de l'échéance proposée.
     */
    public function accepterModification($id): JsonResponse
    {
        $pret = Pret::findOrFail($id);

        if (Auth::id() !== $pret->user_id && Auth::user()->role !== 'admin') {
            return response()->json(['succes' => false, 'message' => 'Non autorisé.'], 403);
        }

        $pret->update([
            'date_echeance' => $pret->date_modification_proposee ?? $pret->date_echeance,
            'est_accepte_par_membre' => true,
            'date_echeance_modifiee' => false,
            'date_modification_proposee' => null,
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Conditions acceptées. Le prêt peut désormais être validé par le trésorier.',
            'pret' => $pret->fresh(),
        ]);
    }

    /**
     * Validation du prêt et décaissement physique de la caisse.
     */
    public function valider($id): JsonResponse
    {
        $pret = Pret::findOrFail($id);

        if ($pret->statut !== 'en_attente') {
            return response()->json(['succes' => false, 'message' => 'Ce prêt a déjà été traité.'], 422);
        }

        if (!$pret->est_accepte_par_membre) {
            return response()->json([
                'succes' => false,
                'message' => "Le membre doit préalablement accepter l'échéance proposée."
            ], 422);
        }

        return DB::transaction(function () use ($pret) {
            $seance = $pret->seance;

            if ($seance->total_encaisse < $pret->montant_demande) {
                return response()->json([
                    'succes' => false,
                    'message' => "Fonds insuffisants en caisse (" . number_format($seance->total_encaisse) . " F disponibles) pour accorder ce prêt de " . number_format($pret->montant_demande) . " F."
                ], 422);
            }

            $seance->total_encaisse -= $pret->montant_demande;
            $seance->save();

            $pret->update([
                'statut' => 'valide',
                'date_echeance_modifiee' => false,
                'date_modification_proposee' => null,
            ]);

            return response()->json([
                'succes' => true,
                'message' => 'Prêt validé et décaissé avec succès.',
                'pret' => $pret->fresh(),
                'total_encaisse_seance' => $seance->total_encaisse,
            ]);
        });
    }

    /**
     * Annulation d'une demande de prêt en attente.
     */
    public function destroy($id): JsonResponse
    {
        $pret = Pret::findOrFail($id);

        if ($pret->statut !== 'en_attente') {
            return response()->json(['succes' => false, 'message' => 'Impossible d\'annuler un prêt déjà validé.'], 422);
        }

        if (Auth::user()->role === 'membre' && $pret->user_id !== Auth::id()) {
            return response()->json(['succes' => false, 'message' => 'Action non autorisée.'], 403);
        }

        $pret->delete();

        return response()->json([
            'succes' => true,
            'message' => 'Demande de prêt annulée avec succès.',
        ]);
    }
}
