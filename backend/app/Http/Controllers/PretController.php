<?php

namespace App\Http\Controllers;

use App\Models\Pret;
use App\Models\Seance;
use App\Models\User;
use App\Models\Cycle;
use App\Http\Requests\StorePretRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PretController extends Controller
{
    /**
     * Liste des prêts (En cours et terminés)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Construction de la requête de base
        if ($user->role === 'membre') {
            // Le membre ne voit que SES prêts
            $query = Pret::with(['seance.cycle'])
                ->where('user_id', $user->id);
        } else {
            // Le trésorier voit TOUT
            $query = Pret::with(['user', 'seance.cycle']);
        }

        // Filtre par statut
        if ($request->has('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        // Filtre par action requise (attente confirmation date)
        if ($request->has('action_requise') && $request->action_requise === 'confirmation_date') {
            $query->where('date_echeance_modifiee', true)
                ->where('est_accepte_par_membre', false)
                ->where('statut', 'en_attente');
        }

        // Filtre par membre (pour trésorier)
        if ($user->role !== 'membre' && $request->has('membre_id') && $request->membre_id) {
            $query->where('user_id', $request->membre_id);
        }

        // Filtre par date (du/au)
        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $prets = $query->get();

        // Récupérer la liste des membres pour le filtre (trésorier seulement)
        $membres = [];
        if ($user->role !== 'membre') {
            $membres = User::where('status', 'actif')->orderBy('name')->get();
        }
// Charger les statistiques pour chaque membre (si trésorier)
        $statsMembres = [];
        if ($user->role !== 'membre') {
            $membresIds = $prets->pluck('user_id')->unique();
            foreach ($membresIds as $membreId) {
                $membre = User::find($membreId);
                if ($membre) {
                    $statsMembres[$membreId] = [
                        'total_prets' => $membre->prets()->count(),
                        'prets_en_attente' => $membre->prets()->where('statut', 'en_attente')->count(),
                        'prets_valides' => $membre->prets()->where('statut', 'valide')->count(),
                        'prets_rembourses' => $membre->prets()->where('statut', 'rembourse')->count(),
                        'prets_en_retard' => $membre->prets()
                            ->where('statut', 'valide')
                            ->where('date_echeance', '<', now())
                            ->count(),
                    ];
                }
            }
        }

        return view('prets.index', compact('prets', 'membres', 'statsMembres'));

    }

    /**
     * Formulaire de demande de prêt
     */
    public function create()
    {
        // On récupère la séance ouverte
        $seance = Seance::where('statut', 'ouverte')->latest('date_seance')->first();

        if (!$seance) {
            return back()->with('error', 'Aucune séance ouverte. Impossible de demander un prêt maintenant.');
        }

        // Si c'est un trésorier, il a besoin de la liste des membres pour choisir qui emprunte
        $membres = [];
        if (Auth::user()->role !== 'membre') {
            $membres = User::where('status', 'actif')->orderBy('name')->get();
        }

        return view('prets.create', compact('seance', 'membres'));
    }

    /**
     * Enregistrement de la demande (Sans toucher à la caisse pour l'instant)
     */
    public function store(StorePretRequest $request)
    {
        // 1. IDENTIFIER QUI EMPRUNTE
        // Si c'est un membre connecté, on prend son ID à lui (Auth::id())
        // Si c'est le trésorier, on prend l'ID qu'il a choisi dans la liste ($request->user_id)
        if ($request->user()->role === 'membre') {
            $userId = $request->user()->id;
        } else {
            $userId = $request->user_id;
        }

        // 2. VÉRIFIER L'ANCIENNETÉ (Supprimé demande client, stocké en documentation)
        // $user = \App\Models\User::findOrFail($userId);
        // $accountAge = ...

        // 3. RECUPERER LA SEANCE ET LE TAUX
        $seance = Seance::findOrFail($request->seance_id);

        // RESTRICTION : Impossible d'ajouter après la date (sauf Admin)
        if (Auth::user()->role !== 'admin' && now()->startOfDay()->gt($seance->date_seance)) {
            return back()->with('error', "Désolé, impossible de demander un prêt après la date de la séance (" . $seance->date_seance->format('d/m/Y') . ").");
        }

        $tauxInteret = $seance->cycle->taux_interet;

        // 4. CALCULER L'INTERET
        $interetTotal = $request->montant_demande * ($tauxInteret / 100);

        // 5. CALCULER LA DATE MAXIMUM AUTORISÉE (3 Mois maximum ET avant la fin du cycle)
        $dateDemandee = \Carbon\Carbon::parse($request->date_echeance)->startOfDay();
        $dateLimite3Mois = now()->addMonths(3)->startOfDay();
        $cycle = $seance->cycle;
        $finCycle = \Carbon\Carbon::parse($cycle->date_fin)->startOfDay();

        // Déterminer la date maximum possible
        $dateMaximum = $dateLimite3Mois->min($finCycle);

        // Vérifier si la date demandée dépasse le maximum autorisé
        if ($dateDemandee->greaterThan($dateMaximum)) {
            // Créer le prêt avec la date ajustée au maximum possible
            $pret = Pret::create([
                'user_id' => $userId,
                'seance_id' => $request->seance_id,
                'montant_demande' => $request->montant_demande,
                'interet_total' => $interetTotal,
                'date_echeance' => $dateMaximum->format('Y-m-d'), // Date ajustée
                'date_echeance_modifiee' => true,
                'date_modification_proposee' => $dateMaximum->format('Y-m-d'),
                'est_accepte_par_membre' => false,
                'statut' => 'en_attente',
            ]);

            // Notifier l'utilisateur
            \App\Models\Annonce::create([
                'titre' => '⚠️ Date ajustée pour votre prêt',
                'message' => "Bonjour " . $pret->user->name . ". Votre demande de prêt a été enregistrée mais la date d'échéance demandée (" . $dateDemandee->format('d/m/Y') . ") dépasse la limite autorisée (max: " . $dateMaximum->format('d/m/Y') . "). Veuillez confirmer la date ajustée.",
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('prets.index')
                ->with('warning', 'Demande enregistrée avec date ajustée à ' . $dateMaximum->format('d/m/Y') . '. Le membre doit confirmer la nouvelle date.');
        } else {
            // Date dans les limites, création normale avec accord tacite du membre
            Pret::create([
                'user_id' => $userId,
                'seance_id' => $request->seance_id,
                'montant_demande' => $request->montant_demande,
                'interet_total' => $interetTotal,
                'date_echeance' => $request->date_echeance,
                'est_accepte_par_membre' => true,
                'date_echeance_modifiee' => false,
                'statut' => 'en_attente',
            ]);

            return redirect()->route('prets.index')
                ->with('success', 'Votre demande de prêt a été envoyée. Attente de validation.');
        }
    }

    /**
     * Affiche le formulaire de modification (Pour le Trésorier)
     */
    public function edit(Pret $pret)
    {
        // Sécurité
        if ($pret->statut !== 'en_attente') {
            return back()->with('error', 'On ne peut modifier qu\'un prêt en attente.');
        }
        // Vérifier si une modification est déjà en attente
        if ($pret->date_echeance_modifiee && !$pret->est_accepte_par_membre) {
            return back()->with('warning', 'Une modification de date est déjà en attente de confirmation par le membre.');
        }

        return view('prets.edit', compact('pret'));
    }

    /**
     * Enregistre la modification du Trésorier
     */
    public function update(Request $request, Pret $pret)
    {
        // 1. SÉCURITÉ : Seul le trésorier/admin peut modifier une demande
        if ($request->user()->role === 'membre') {
            abort(403, "Action non autorisée.");
        }

        // Débogage : voir ce qui arrive
         //dd($request->all(), $pret->toArray());

        // 2. Récupérer les dates AVANT validation
        $ancienneDate = \Carbon\Carbon::parse($pret->date_echeance);
        $nouvelleDate = \Carbon\Carbon::parse($request->date_echeance);

        // 3. VALIDATION avec messages clairs
        $validator = \Validator::make($request->all(), [
            'date_echeance' => [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) use ($ancienneDate, $nouvelleDate) {
                    if ($nouvelleDate->lessThan($ancienneDate)) {
                        $fail('La nouvelle date d\'échéance ne peut pas être antérieure à la date actuelle (' . $ancienneDate->format('d/m/Y') . ').');
                    }
                }
            ],
            'montant_demande' => [
                'required',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) use ($pret) {
                    // Empêcher la modification du montant
                    if ($value != $pret->montant_demande) {
                        $fail('Vous ne pouvez pas modifier le montant du prêt. Montant actuel : ' . number_format($pret->montant_demande, 0, ',', ' ') . ' F');
                    }
                }
            ]
        ], [
            'date_echeance.after_or_equal' => 'La date doit être égale ou postérieure à aujourd\'hui.',
            'montant_demande.min' => 'Le montant minimum est 1000 FCFA.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 4. VÉRIFIER LES LIMITES (3 mois et fin de cycle)
        $seance = $pret->seance;
        $cycle = $seance->cycle;

        $dateLimite3Mois = now()->addMonths(3)->startOfDay();
        $finCycle = \Carbon\Carbon::parse($cycle->date_fin)->startOfDay();
        $dateMaximum = $dateLimite3Mois->min($finCycle);

        if ($nouvelleDate->greaterThan($dateMaximum)) {
            return back()->with('error', 'La date ne peut pas dépasser ' . $dateMaximum->format('d/m/Y') . ' (3 mois maximum ou fin du cycle)')->withInput();
        }

        // 5. DÉTECTION DES CHANGEMENTS
        $dateModifiee = !$ancienneDate->eq($nouvelleDate);

        // 6. Si la date n'a pas changé, simplement mettre à jour
        if (!$dateModifiee) {
            // Pas de changement de date
            return redirect()->route('prets.index')
                ->with('info', 'Aucune modification apportée.');
        }

        // 7. Si la date a changé, vérifier si le membre a déjà une modification en attente
        if ($pret->date_echeance_modifiee && !$pret->est_accepte_par_membre) {
            return back()->with('warning', 'Ce prêt a déjà une modification de date en attente de confirmation par le membre.')->withInput();
        }

        // 8. Calculer le nouvel intérêt si nécessaire (même si montant inchangé)
        $tauxInteret = $pret->seance->cycle->taux_interet;
        $interetTotal = $pret->montant_demande * ($tauxInteret / 100);

        // 9. MISE À JOUR DU PRÊT - Option A : La date change immédiatement
        try {
            $pret->update([
                'date_echeance' => $request->date_echeance,
                'montant_demande' => $request->montant_demande, // Normalement inchangé
                'interet_total' => $interetTotal,
                'date_echeance_modifiee' => true,
                'est_accepte_par_membre' => false,
                'date_modification_proposee' => $request->date_echeance,
            ]);

            // Débogage : vérifier la mise à jour
            // dd('Mise à jour effectuée', $pret->fresh()->toArray());

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())->withInput();
        }

        // 10. ENVOI DE L'ANNONCE si modification de date
        $membre = $pret->user;
        try {
            $annonce = \App\Models\Annonce::create([
                'titre' => '⚠️ Date d\'échéance modifiée - Prêt de ' . $membre->name,
                'message' => "Bonjour " . $membre->name . ". Le trésorier a modifié la date d'échéance de votre prêt.<br><br>" .
                    "• <strong>Ancienne date</strong> : " . $ancienneDate->format('d/m/Y') . "<br>" .
                    "• <strong>Nouvelle date</strong> : " . $nouvelleDate->format('d/m/Y') . "<br><br>" .
                    "Merci de confirmer votre accord avant validation finale du prêt.",
                'user_id' => $request->user()->id,
            ]);

            // Associer l'annonce au membre concerné
            $annonce->readers()->attach($membre->id);

        } catch (\Exception $e) {
            // On continue même si l'annonce échoue
            \Log::error('Erreur création annonce: ' . $e->getMessage());
        }

        return redirect()->route('prets.index')
            ->with('success', 'Date d\'échéance modifiée de ' . $ancienneDate->format('d/m/Y') . ' à ' . $nouvelleDate->format('d/m/Y') . '. Le membre doit confirmer la nouvelle date.');
    }

    // Ajoute aussi la méthode destroy pour qu'un membre puisse annuler sa demande si elle n'est pas encore validée
    public function destroy(Pret $pret)
    {
        // Sécurité : On ne peut supprimer que SI c'est "en_attente"
        if ($pret->statut !== 'en_attente') {
            return back()->with('error', 'Impossible de supprimer un prêt déjà validé ou remboursé.');
        }

        // Sécurité : Un membre ne peut supprimer que SA propre demande
        if (Auth::user()->role === 'membre' && $pret->user_id !== Auth::id()) {
            abort(403);
        }

        $pret->delete();
        return back()->with('success', 'Demande annulée.');
    }

    /**
     * Action CRITIQUE : Valider le prêt et sortir l'argent
     */
    public function valider(Pret $pret)
    {
        if ($pret->statut !== 'en_attente') {
            return back()->with('error', 'Ce prêt a déjà été traité.');
        }

        // --- SÉCURITÉ RENFORCÉE ---
        if ($pret->date_echeance_modifiee && !$pret->est_accepte_par_membre) {
            return back()->with('error', 'Impossible de valider : Le membre doit d\'abord accepter la nouvelle date d\'échéance.');
        }

        if (!$pret->est_accepte_par_membre) {
            return back()->with('error', 'Impossible de valider : Le membre n\'a pas encore accepté les conditions.');
        }

        return DB::transaction(function () use ($pret) {
            $seance = $pret->seance;

            // 1. Vérifier si la caisse a assez d'argent
            if ($seance->total_encaisse < $pret->montant_demande) {
                return back()->with('error', 'Fonds insuffisants dans la séance pour accorder ce prêt !');
            }

            // 2. Débiter la caisse
            $seance->total_encaisse -= $pret->montant_demande;
            $seance->save();

            // 3. Changer le statut du prêt et réinitialiser les flags
            $pret->update([
                'statut' => 'valide',
                'date_echeance_modifiee' => false,
                'date_modification_proposee' => null
            ]);

            return back()->with('success', 'Prêt accordé ! Le montant a été retiré de la caisse.');
        });
    }
    public function accepterModification(Pret $pret)
    {
        // Sécurité : Seul le propriétaire du prêt peut accepter
        if (Auth::id() !== $pret->user_id) {
            abort(403);
        }

        // Si une modification de date est en attente
        if ($pret->date_echeance_modifiee) {
            // Mettre à jour la date avec celle qui a été proposée (ou conserver l'actuelle en fallback)
            $pret->update([
                'date_echeance' => $pret->date_modification_proposee ?? $pret->date_echeance,
                'est_accepte_par_membre' => true,
                'date_echeance_modifiee' => false,
                'date_modification_proposee' => null
            ]);

            return back()->with('success', 'Vous avez accepté la nouvelle date d\'échéance. Le trésorier peut maintenant valider le prêt.');
        } else {
            // Si pas de modification de date, juste accepter
            $pret->update(['est_accepte_par_membre' => true]);

            return back()->with('success', 'Vous avez accepté les conditions. Le trésorier peut maintenant valider le prêt.');
        }
    }




    // (Ajoute ici destroy() si tu veux pouvoir supprimer une demande erronée)
}
