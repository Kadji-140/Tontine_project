<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cycle;
use App\Models\Seance;
use App\Models\Cotisation;
use App\Models\Pret;
use App\Models\Sanction;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cycleActif = Cycle::where('est_actif', true)->first();

        // --- DONNÉES COMMUNES À TOUS ---

        // 1. Prochaine séance (corrigée)
        $prochaineSeance = Seance::where('date_seance', '>=', now()->startOfDay())
            ->orderBy('date_seance', 'asc')
            ->first();

        // 2. Graphiques (pour tous)
        $seancesGraph = Seance::orderBy('date_seance', 'asc')->take(10)->get();
        $labelsGraph = $seancesGraph->map(fn($s) => $s->date_seance->format('d/M'))->toArray();
        $dataGraph = $seancesGraph->pluck('total_encaisse')->toArray();

        $totalTontine = Cotisation::where('type', 'tontine')->sum('montant');
        $totalSecours = Cotisation::where('type', 'secours')->sum('montant');


        // --- LOGIQUE PAR RÔLE ---

        if ($user->role === 'admin' || $user->role === 'tresorier') {
            // --- Données Admin (Calcul précis) ---
            
            // 1. Solde Caisse Global (Toutes les entrées - Toutes les sorties validées)
            $totalCotisations = Cotisation::sum('montant');
            $totalRemboursements = \App\Models\Remboursement::sum('montant');
            $totalFonds = \App\Models\FondsDepense::sum('montant');
            
            $totalPretsSortis = Pret::where('statut', 'valide')->sum('montant_demande');
            $totalDepenses = \App\Models\Depense::where('statut', 'validee')->sum('montant');
            
            $soldeCaisse = ($totalCotisations + $totalRemboursements + $totalFonds) - ($totalPretsSortis + $totalDepenses);

            // 2. Argent Dehors (Capital Restant Dû sur les prêts valides)
            // Calculer prêt par prêt pour être précis : Montant Demandé - Déjà Remboursé
            $pretsActifs = Pret::where('statut', 'valide')->get();
            $argentDehors = 0;
            foreach ($pretsActifs as $pret) {
                $rembourse = $pret->remboursements->sum('montant');
                $reste = $pret->montant_demande - $rembourse; // On ne compte que le capital ici, ou capital+intérêts ? Souvent capital pour la trésorerie.
                // Si on veut suivre la dette totale (avec intérêts) : $pret->total_a_rembourser - $rembourse
                // Restons sur la trésorerie simple (Argent sorti non rentré) pour l'instant, ou la dette comptable ?
                // Le user a dit "Crédits Dehors". Généralement c'est ce que les gens doivent.
                $argentDehors += max(0, $pret->total_a_rembourser - $rembourse);
            }

            $activites = Cotisation::with('user')->latest()->take(5)->get();

            return view('dashboard', compact(
                'user', 'cycleActif', 'prochaineSeance', 'soldeCaisse',
                'argentDehors', 'activites', 'labelsGraph', 'dataGraph',
                'totalTontine', 'totalSecours'
            ));
        } else {
            // ... (Code membre inchangé pour l'instant, sauf si besoin) ...
             // --- DONNÉES MEMBRE ---

            // 1. Mon épargne (tontine seulement)
            $monEpargne = Cotisation::where('user_id', $user->id)
                ->where('cycle_id', $cycleActif->id ?? null)
                ->where('type', 'tontine')
                ->sum('montant');

            // 2. Mes prêts en cours
            $mesPrets = Pret::where('user_id', $user->id)
                ->whereIn('statut', ['valide', 'en_cours'])
                ->sum('montant_demande');

            // 3. Restant à payer (prêts validés seulement)
            $restantDu = 0;
            $mesPretsValides = Pret::where('user_id', $user->id)->where('statut', 'valide')->get();
            foreach($mesPretsValides as $p) {
                $restantDu += max(0, $p->total_a_rembourser - $p->remboursements->sum('montant'));
            }

            // 4. Mes dettes TOTALES
            $mesDettes = $restantDu;

            // 5. Mes sanctions (amendes, retards)
            $mesSanctions = Sanction::where('user_id', $user->id)
                ->where('statut', 'non_payee')
                ->sum('montant');

            // 6. Total cotisations (tontine + secours)
            $totalCotisations = Cotisation::where('user_id', $user->id)
                ->where('cycle_id', $cycleActif->id ?? null)
                ->sum('montant');

            return view('dashboard', compact(
                'user', 'cycleActif', 'prochaineSeance',
                'monEpargne', 'mesPrets', 'restantDu',
                'mesDettes', 'mesSanctions', 'totalCotisations',
                'labelsGraph', 'dataGraph', 'totalTontine', 'totalSecours'
            ));
        }
    }

    public function getChartData(Request $request)
    {
        $filter = $request->query('filter', 'month'); // today, month, year, all (par défaut month)
        $queryDate = now();

        $labels = [];
        $dataEncaisse = [];
        $dataDepenses = [];
        $dataBanque = [];

        // Calcul du solde initial avant la période choisie (pour la courbe cumulative)
        // Solde = (Cotisations + Remboursements + Fonds) - (Dépenses + Prêts)
        // Note: C'est approximatif si on ne filtre pas par 'statut' validé dans le passé, mais on garde la cohérence avec le présent.
        
        $initialBalance = 0;
        // Optimisation : On ne calcule le solde initial que si ce n'est pas 'all' (tout l'historique)
        if ($filter !== 'all') {
             $start = $queryDate; // Sera écrasé par le switch
             // Recalcul rapide du solde jusqu'à la date de début
             // On doit définir $startDate AVANT la boucle
        }

        // On va agréger par jour pour 'month', par mois pour 'year'
        if ($filter === 'today') {
             $startDate = today()->subDays(6);
             $endDate = today()->endOfDay();
             $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
             
             // Calcul du solde initial à la veille de startDate
             $initialBalance = $this->calculateBalanceAt($startDate->copy()->subSecond());
             $currentBalance = $initialBalance;

             foreach($period as $date) {
                 $labels[] = $date->format('d/m');
                 
                 $encaisse = Cotisation::whereDate('created_at', $date)->sum('montant') 
                           + \App\Models\Remboursement::whereDate('created_at', $date)->sum('montant')
                           + \App\Models\FondsDepense::whereDate('created_at', $date)->sum('montant');
                 
                 $sorties = \App\Models\Depense::whereDate('created_at', $date)->where('statut', 'validee')->sum('montant')
                          + Pret::whereDate('updated_at', $date)->where('statut', 'valide')->sum('montant_demande');
                 
                 $dataEncaisse[] = $encaisse;
                 $dataDepenses[] = $sorties;
                 
                 $currentBalance += ($encaisse - $sorties);
                 $dataBanque[] = $currentBalance;
             }
             
        } elseif ($filter === 'month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
             $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
             
             $initialBalance = $this->calculateBalanceAt($startDate->copy()->subSecond());
             $currentBalance = $initialBalance;

             foreach($period as $date) {
                 $labels[] = $date->format('d');
                 
                 $encaisse = Cotisation::whereDate('created_at', $date)->sum('montant') 
                           + \App\Models\Remboursement::whereDate('created_at', $date)->sum('montant')
                           + \App\Models\FondsDepense::whereDate('created_at', $date)->sum('montant');

                 $sorties = \App\Models\Depense::whereDate('created_at', $date)->where('statut', 'validee')->sum('montant')
                          + Pret::whereDate('updated_at', $date)->where('statut', 'valide')->sum('montant_demande');

                 $dataEncaisse[] = $encaisse;
                 $dataDepenses[] = $sorties;
                 
                 $currentBalance += ($encaisse - $sorties);
                 $dataBanque[] = $currentBalance;
             }

        } elseif ($filter === 'year') {
            $initialBalance = $this->calculateBalanceAt(now()->startOfYear()->subSecond());
            $currentBalance = $initialBalance;

            for ($m = 1; $m <= 12; $m++) {
                $labels[] = date('M', mktime(0, 0, 0, $m, 1));
                
                $encaisse = Cotisation::whereMonth('created_at', $m)->whereYear('created_at', now()->year)->sum('montant')
                          + \App\Models\Remboursement::whereMonth('created_at', $m)->whereYear('created_at', now()->year)->sum('montant')
                          + \App\Models\FondsDepense::whereMonth('created_at', $m)->whereYear('created_at', now()->year)->sum('montant');
                
                $sorties = \App\Models\Depense::whereMonth('created_at', $m)->whereYear('created_at', now()->year)->where('statut', 'validee')->sum('montant')
                         + Pret::whereMonth('updated_at', $m)->whereYear('updated_at', now()->year)->where('statut', 'valide')->sum('montant_demande');
                
                $dataEncaisse[] = $encaisse;
                $dataDepenses[] = $sorties;
                
                $currentBalance += ($encaisse - $sorties);
                $dataBanque[] = $currentBalance;
            }
        } 

        return response()->json([
            'labels' => $labels,
            'encaisse' => $dataEncaisse,
            'depenses' => $dataDepenses,
            'banque' => $dataBanque
        ]);
    }

    private function calculateBalanceAt($date) {
        $enc = Cotisation::where('created_at', '<=', $date)->sum('montant')
             + \App\Models\Remboursement::where('created_at', '<=', $date)->sum('montant')
             + \App\Models\FondsDepense::where('created_at', '<=', $date)->sum('montant');
             
        $dec = \App\Models\Depense::where('created_at', '<=', $date)->where('statut', 'validee')->sum('montant')
             + Pret::where('updated_at', '<=', $date)->where('statut', 'valide')->sum('montant_demande');
             
        return $enc - $dec;
    }
}
