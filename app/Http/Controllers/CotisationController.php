<?php

namespace App\Http\Controllers;

use App\Models\Cotisation;
use App\Models\Seance;
use App\Models\FondsDepense;
use App\Http\Requests\StoreCotisationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- Importante pour la transaction
use Illuminate\Http\RedirectResponse;

class CotisationController extends Controller
{
    public function store(StoreCotisationRequest $request): RedirectResponse
    {
        // Début de la transaction atomique
        // Si une ligne échoue à l'intérieur, TOUT est annulé (Rollback)
        return DB::transaction(function () use ($request) {
            // 1. Récupération des données et de l'utilisateur
            $data = $request->validated();
            $data['enregistre_par'] = Auth::id();
            $cycle = Seance::find($request->seance_id)->cycle;
            
            // 2. Gestion du "Mange-Mille" (Frais de séance / Collation)
            // On vérifie si le membre a déjà payé son Mange-Mille pour cette séance
            $dejaPaye = FondsDepense::where('seance_id', $request->seance_id)
                ->where('user_id', $request->user_id)
                ->where('type', 'mange_mille')
                ->exists();

            $fraisMangeMille = 0;

            // Si pas encore payé et que le cycle prévoit un montant > 0
            if (!$dejaPaye && $cycle->montant_mange_mille > 0) {
                $montantMangeMille = $cycle->montant_mange_mille;
                
                // Vérification que le montant donné suffit pour au moins couvrir le mange-mille
                if ($request->montant < $montantMangeMille) {
                    throw new \Exception("Le montant (" . number_format($request->montant) . " F) est insuffisant pour couvrir le Mange-Mille obligatoire de " . number_format($montantMangeMille) . " F.");
                }

                // On prélève le Mange-Mille
                FondsDepense::create([
                    'cycle_id' => $cycle->id,
                    'seance_id' => $request->seance_id,
                    'user_id' => $request->user_id,
                    'type' => 'mange_mille',
                    'montant' => $montantMangeMille,
                    'description' => 'Frais de séance (automatique)'
                ]);

                // On réduit le montant de la cotisation du montant prélevé
                $data['montant'] = $request->montant - $montantMangeMille;
                $fraisMangeMille = $montantMangeMille;
            }

            // 3. Enregistrement de la Cotisation (avec le reste)
            // Si le reste est > 0 (cas normal), on crée la cotisation
            if ($data['montant'] > 0) {
                Cotisation::create($data);
                
                // Mise à jour du solde de la séance (uniquement la partie cotisation, le mange-mille est compté à part ou inclus ? 
                // Généralement le 'Total Encaisse' de la séance inclut TOUT l'argent physique sur la table)
                // Donc on ajoute le montant INITIAL complet ($request->montant) au solde physique de la séance
                $seance = Seance::findOrFail($request->seance_id);
                $seance->total_encaisse += $request->montant; 
                $seance->save();

                $msg = 'Cotisation enregistrée.';
                if ($fraisMangeMille > 0) {
                    $msg .= " (Dont " . number_format($fraisMangeMille) . " F de Mange-Mille prélevés)";
                }
                return back()->with('success', $msg);
            } else {
                // Cas rare où le membre donne JUSTE le montant du mange-mille et 0 pour la tontine
                // On met à jour le solde de la séance quand même
                $seance = Seance::findOrFail($request->seance_id);
                $seance->total_encaisse += $request->montant; 
                $seance->save();
                
                return back()->with('success', 'Frais de Mange-Mille (' . number_format($fraisMangeMille) . ' F) enregistrés. Aucune cotisation ajoutée (Reste = 0).');
            }
        });
    }

    public function destroy(Cotisation $cotisation): RedirectResponse
    {
        return DB::transaction(function () use ($cotisation) {
            // 1. On récupère la séance avant de supprimer pour ajuster le solde
            $seance = $cotisation->seance;

            // 2. On retire le montant du solde de la séance
            $seance->total_encaisse -= $cotisation->montant;
            $seance->save();

            // 3. On supprime la cotisation
            $cotisation->delete();

            return back()->with('success', 'Cotisation annulée et solde ajusté.');
        });
    }
}
