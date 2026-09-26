<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Http\Requests\StoreCycleRequest;
use App\Http\Requests\UpdateCycleRequest;
use Illuminate\Http\Request;

class CycleController extends Controller
{
    /**
     * Affiche la liste des cycles.
     */
    public function index()
    {
        // On récupère tous les cycles, du plus récent au plus ancien
        $cycles = Cycle::orderBy('date_debut', 'desc')->get();
        return view('cycles.index', compact('cycles'));
    }

    /**
     * Affiche le formulaire de création.
     */
    public function create()
    {
        return view('cycles.create');
    }

    /**
     * Enregistre un nouveau cycle dans la BDD.
     */
    public function store(StoreCycleRequest $request)
    {
        // 1. Validation automatique via StoreCycleRequest
        // Si on est ici, c'est que les données sont valides.

        // 2. Logique métier : Si ce cycle est défini comme "Actif", on désactive les autres.
        // (Note : Pour l'instant on le crée simplement, on gérera l'activation active plus tard ou via une checkbox)

        Cycle::create($request->validated());

        // 3. Redirection avec message de succès
        return redirect()->route('cycles.index')
            ->with('success', 'Cycle créé avec succès.');
    }

    /**
     * Affiche le formulaire d'édition.
     */
    public function edit(Cycle $cycle)
    {
        return view('cycles.edit', compact('cycle'));
    }

    /**
     * Met à jour le cycle.
     */
    public function update(UpdateCycleRequest $request, Cycle $cycle)
    {
        $cycle->update($request->validated());

        return redirect()->route('cycles.index')
            ->with('success', 'Cycle mis à jour.');
    }

    /**
     * Supprime le cycle.
     */
    public function destroy(Cycle $cycle)
    {
        // Sécurité : On ne supprime pas un cycle s'il contient déjà des données (séances)
        if ($cycle->seances()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer ce cycle car il contient des séances.');
        }

        $cycle->delete();

        return redirect()->route('cycles.index')
            ->with('success', 'Cycle supprimé.');
    }

    /**
     * Méthode personnalisée pour "Activer" un cycle
     * (Il faudra ajouter une route pour ça plus tard)
     */
    public function makeActive(Cycle $cycle)
    {
        // 1. Désactiver tous les cycles
        Cycle::query()->update(['est_actif' => false]);

        // 2. Activer celui-ci
        $cycle->update(['est_actif' => true]);

        return back()->with('success', 'Le cycle ' . $cycle->nom . ' est désormais actif.');
    }

    /**
     * Affiche la répartition des intérêts (BANQUE)
     */
    public function showDistribution(Cycle $cycle)
    {
        // 1. Calculer l'Assiette Totale (Somme des cotisations BANQUE)
        $totalAssiette = \App\Models\Cotisation::whereHas('seance', function($q) use ($cycle) {
            $q->where('cycle_id', $cycle->id);
        })->where('type', 'banque')->sum('montant');

        // 2. Calculer le Bénéfice Total (Intérêts des prêts validés/remboursés)
        // C'est la source réelle des intérêts à distribuer
        $totalBenefice = \App\Models\Pret::whereHas('seance', function($q) use ($cycle) {
            $q->where('cycle_id', $cycle->id);
        })->whereIn('statut', ['valide', 'rembourse'])
          ->sum('interet_total');

        // 3. Calculer le taux de rendement réel (x dans la formule)
        // Formule correcte: Total_déposé × x = Total_déposé + Total_intérêts
        // Donc: x = (Total_déposé + Total_intérêts) / Total_déposé
        $tauxRendementReel = ($totalAssiette > 0) ? (($totalAssiette + $totalBenefice) / $totalAssiette) : 0;

        // 4. Répartition par Membre
        // Chaque membre reçoit: Son_dépôt × (1 + x)
        $membresIds = \App\Models\Cotisation::whereHas('seance', function($q) use ($cycle) {
            $q->where('cycle_id', $cycle->id);
        })->where('type', 'banque')->distinct()->pluck('user_id');
        
        $repartition = [];

        foreach($membresIds as $id) {
            $user = \App\Models\User::find($id);
            if(!$user) continue;

            // Calculer son apport personnel en BANQUE sur tout le cycle
            $apport = \App\Models\Cotisation::whereHas('seance', function($q) use ($cycle) {
                $q->where('cycle_id', $cycle->id);
            })->where('user_id', $id)->where('type', 'banque')->sum('montant');

            // Calculer son pourcentage (Part du capital total)
            $pourcentage = ($totalAssiette > 0) ? ($apport / $totalAssiette) : 0;

            // Calculer son gain basé sur SON APPORT et le TAUX RÉEL
            // x = (Total_déposé + Total_intérêts) / Total_déposé
            // Total à percevoir = apport × x
            // Gain (intérêt seulement) = (apport × x) - apport
            $totalAPercevoir = $apport * $tauxRendementReel;
            $gain = $totalAPercevoir - $apport;

            $repartition[] = [
                'user' => $user,
                'apport' => $apport,
                'pourcentage' => $pourcentage * 100, // Pour affichage
                'gain' => $gain,
                'total_a_percevoir' => $totalAPercevoir
            ];
        }

        // Trier par nom
        usort($repartition, function($a, $b) {
            return strcasecmp($a['user']->name, $b['user']->name);
        });

        return view('cycles.distribution', compact('cycle', 'totalAssiette', 'totalBenefice', 'repartition', 'tauxRendementReel'));
    }

    /**
     * Affiche la liste des membres du cycle avec leur rang
     */
    public function membres(Cycle $cycle)
    {
        // Charger les membres avec leur rang
        $membres = $cycle->membres;

        // Récupérer les utilisateurs actifs qui ne sont PAS encore dans ce cycle
        $usersDisponibles = \App\Models\User::where('status', 'actif')
            ->whereNotIn('id', $membres->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('cycles.membres', compact('cycle', 'membres', 'usersDisponibles'));
    }

    /**
     * Affiche l'ordre de passage (Vue publique / Membre)
     */
    public function publicOrder(Cycle $cycle)
    {
        $membres = $cycle->membres;
        return view('cycles.public_order', compact('cycle', 'membres'));
    }

    /**
     * Ajouter un membre au cycle
     */
    public function storeMembre(Request $request, Cycle $cycle)
    {
        // Sécurité
        if (auth()->user()->role === 'membre') {
            abort(403, 'Action non autorisée.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Vérifier que le membre n'est pas déjà dans le cycle
        if ($cycle->membres()->where('user_id', $request->user_id)->exists()) {
            return back()->with('error', 'Ce membre fait déjà partie du cycle.');
        }

        // Calculer le prochain rang (max + 1)
        $maxRang = $cycle->membres()->max('rang') ?? 0;

        $cycle->membres()->attach($request->user_id, [
            'rang' => $maxRang + 1
        ]);

        return back()->with('success', 'Membre ajouté avec succès.');
    }

    /**
     * Mélanger aléatoirement l'ordre des membres
     */
    public function randomizeRanks(Cycle $cycle)
    {
        // Sécurité : Seul admin/trésorier peut modifier l'ordre
        if (auth()->user()->role === 'membre') {
            abort(403, 'Action non autorisée.');
        }

        $membres = $cycle->membres;
        
        // Check if payments exist for this cycle
        if ($cycle->paiements()->exists()) {
             return back()->with('error', 'Impossible de mélanger l\'ordre : le cycle a déjà démarré.');
        }

        if ($membres->isEmpty()) {
            return back()->with('error', 'Aucun membre à mélanger.');
        }

        // Créer un tableau d'IDs et le mélanger
        $ids = $membres->pluck('id')->toArray();
        shuffle($ids);

        // Réassigner les rangs de 1 à N
        foreach ($ids as $index => $userId) {
            $cycle->membres()->updateExistingPivot($userId, [
                'rang' => $index + 1
            ]);
        }

        return back()->with('success', 'Ordre mélangé avec succès !');
    }

    /**
     * Mettre à jour manuellement les rangs
     */
    public function updateRanks(Request $request, Cycle $cycle)
    {
        // Sécurité : Seul admin/trésorier peut modifier l'ordre
        if (auth()->user()->role === 'membre') {
            abort(403, 'Action non autorisée.');
        }

        // Check if payments exist for this cycle
        if ($cycle->paiements()->exists()) {
             return back()->with('error', 'Impossible de modifier l\'ordre : le cycle a déjà démarré (des paiements ont été effectués).');
        }

        $request->validate([
            'rangs' => 'required|array',
            'rangs.*' => 'required|integer|min:1'
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $cycle) {
                foreach ($request->rangs as $userId => $rang) {
                    $cycle->membres()->updateExistingPivot($userId, [
                        'rang' => $rang
                    ]);
                }
            });

            return redirect()->route('cycles.membres', $cycle)->with('success', 'Ordre des membres mis à jour avec succès.');
        } catch (\Exception $e) {
            \Illuminate\Support\Log::error("Erreur lors de la mise à jour des rangs: " . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'enregistrement de l\'ordre.');
        }
    }

    /**
     * Retirer un membre du cycle
     */
    public function destroyMembre(Cycle $cycle, \App\Models\User $user)
    {
        // Sécurité
        if (auth()->user()->role === 'membre') {
            abort(403, 'Action non autorisée.');
        }

        $cycle->membres()->detach($user->id);

        return back()->with('success', 'Membre retiré du cycle.');
    }
}
