<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use App\Models\Cycle;
use App\Models\Depense;
use App\Models\FondsDepense;
use App\Models\Pret;
use App\Models\Remboursement;
use App\Models\Sanction;
use App\Models\Seance;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Données du tableau de bord adaptées au rôle (Bureau vs Membre).
     */
    public function index(): JsonResponse
    {
        $utilisateur = Auth::user();
        $cycleActif = Cycle::where('est_actif', true)->first();

        // 1. Prochaine séance planifiée
        $prochaineSeance = Seance::where('date_seance', '>=', now()->startOfDay())
            ->orderBy('date_seance', 'asc')
            ->first();

        // 2. Séances récentes pour aperçu graphique
        $seancesGraph = Seance::orderBy('date_seance', 'asc')->take(10)->get();
        $labelsGraph = $seancesGraph->map(fn($s) => $s->date_seance->format('d/M'))->toArray();
        $dataGraph = $seancesGraph->pluck('total_encaisse')->toArray();

        $totalTontine = Cotisation::where('type', 'tontine')->sum('montant');
        $totalSecours = Cotisation::where('type', 'secours')->sum('montant');

        $estBureau = in_array($utilisateur->role, ['admin', 'tresorier']);

        if ($estBureau) {
            // --- DONNÉES DU BUREAU (Admin / Trésorier) ---
            $totalCotisations = Cotisation::sum('montant');
            $totalRemboursements = Remboursement::sum('montant');
            $totalFonds = FondsDepense::sum('montant');

            $totalPretsSortis = Pret::where('statut', 'valide')->sum('montant_demande');
            $totalDepenses = Depense::where('statut', 'validee')->sum('montant');

            $soldeCaisse = ($totalCotisations + $totalRemboursements + $totalFonds) - ($totalPretsSortis + $totalDepenses);

            // Argent dehors : capital restant dû + intérêts sur les prêts en cours
            $pretsActifs = Pret::where('statut', 'valide')->with('remboursements')->get();
            $argentDehors = 0;
            foreach ($pretsActifs as $pret) {
                $rembourse = $pret->remboursements->sum('montant');
                $argentDehors += max(0, $pret->total_a_rembourser - $rembourse);
            }

            $activitesRecentes = Cotisation::with('user')->latest()->take(5)->get();

            return response()->json([
                'succes' => true,
                'est_bureau' => true,
                'cycle_actif' => $cycleActif,
                'prochaine_seance' => $prochaineSeance,
                'solde_caisse' => $soldeCaisse,
                'argent_dehors' => $argentDehors,
                'total_cotisations' => $totalCotisations,
                'total_remboursements' => $totalRemboursements,
                'total_depenses' => $totalDepenses,
                'total_tontine' => $totalTontine,
                'total_secours' => $totalSecours,
                'activites_recentes' => $activitesRecentes,
                'graphique_apercu' => [
                    'labels' => $labelsGraph,
                    'donnees' => $dataGraph,
                ],
            ]);
        }

        // --- DONNÉES DU MEMBRE ---
        $monEpargne = Cotisation::where('user_id', $utilisateur->id)
            ->when($cycleActif, function ($query, $cycle) {
                $query->whereHas('seance', fn($q) => $q->where('cycle_id', $cycle->id));
            })
            ->where('type', 'tontine')
            ->sum('montant');

        $mesPrets = Pret::where('user_id', $utilisateur->id)
            ->whereIn('statut', ['valide', 'en_cours'])
            ->sum('montant_demande');

        $restantDu = 0;
        $mesPretsValides = Pret::where('user_id', $utilisateur->id)
            ->where('statut', 'valide')
            ->with('remboursements')
            ->get();

        foreach ($mesPretsValides as $p) {
            $restantDu += max(0, $p->total_a_rembourser - $p->remboursements->sum('montant'));
        }

        $mesSanctions = Sanction::where('user_id', $utilisateur->id)
            ->where('est_reglee', false)
            ->sum('montant');

        $totalCotisations = Cotisation::where('user_id', $utilisateur->id)
            ->when($cycleActif, function ($query, $cycle) {
                $query->whereHas('seance', fn($q) => $q->where('cycle_id', $cycle->id));
            })
            ->sum('montant');

        return response()->json([
            'succes' => true,
            'est_bureau' => false,
            'cycle_actif' => $cycleActif,
            'prochaine_seance' => $prochaineSeance,
            'mon_epargne' => $monEpargne,
            'mes_prets' => $mesPrets,
            'restant_du' => $restantDu,
            'mes_dettes' => $restantDu,
            'mes_sanctions' => $mesSanctions,
            'total_cotisations' => $totalCotisations,
            'total_tontine' => $totalTontine,
            'total_secours' => $totalSecours,
            'graphique_apercu' => [
                'labels' => $labelsGraph,
                'donnees' => $dataGraph,
            ],
        ]);
    }

    /**
     * Données détaillées pour le graphique financier (Recharts).
     */
    public function donneesGraphique(Request $request): JsonResponse
    {
        $filtre = $request->query('filtre', 'month'); // 'today', 'month', 'year'

        $labels = [];
        $dataEncaisse = [];
        $dataDepenses = [];
        $dataBanque = [];

        if ($filtre === 'today') {
            $dateDebut = today()->subDays(6);
            $dateFin = today()->endOfDay();
            $periode = CarbonPeriod::create($dateDebut, $dateFin);

            $soldeInitial = $this->calculerSoldeA($dateDebut->copy()->subSecond());
            $soldeCourant = $soldeInitial;

            foreach ($periode as $date) {
                $labels[] = $date->format('d/m');

                $encaisse = Cotisation::whereDate('created_at', $date)->sum('montant')
                    + Remboursement::whereDate('created_at', $date)->sum('montant')
                    + FondsDepense::whereDate('created_at', $date)->sum('montant');

                $sorties = Depense::whereDate('created_at', $date)->where('statut', 'validee')->sum('montant')
                    + Pret::whereDate('updated_at', $date)->where('statut', 'valide')->sum('montant_demande');

                $dataEncaisse[] = (float)$encaisse;
                $dataDepenses[] = (float)$sorties;

                $soldeCourant += ($encaisse - $sorties);
                $dataBanque[] = (float)$soldeCourant;
            }
        } elseif ($filtre === 'year') {
            $soldeInitial = $this->calculerSoldeA(now()->startOfYear()->subSecond());
            $soldeCourant = $soldeInitial;

            for ($mois = 1; $mois <= 12; $mois++) {
                $labels[] = date('M', mktime(0, 0, 0, $mois, 1));

                $encaisse = Cotisation::whereMonth('created_at', $mois)->whereYear('created_at', now()->year)->sum('montant')
                    + Remboursement::whereMonth('created_at', $mois)->whereYear('created_at', now()->year)->sum('montant')
                    + FondsDepense::whereMonth('created_at', $mois)->whereYear('created_at', now()->year)->sum('montant');

                $sorties = Depense::whereMonth('created_at', $mois)->whereYear('created_at', now()->year)->where('statut', 'validee')->sum('montant')
                    + Pret::whereMonth('updated_at', $mois)->whereYear('updated_at', now()->year)->where('statut', 'valide')->sum('montant_demande');

                $dataEncaisse[] = (float)$encaisse;
                $dataDepenses[] = (float)$sorties;

                $soldeCourant += ($encaisse - $sorties);
                $dataBanque[] = (float)$soldeCourant;
            }
        } else {
            // Par défaut : mois en cours
            $dateDebut = now()->startOfMonth();
            $dateFin = now()->endOfMonth();
            $periode = CarbonPeriod::create($dateDebut, $dateFin);

            $soldeInitial = $this->calculerSoldeA($dateDebut->copy()->subSecond());
            $soldeCourant = $soldeInitial;

            foreach ($periode as $date) {
                $labels[] = $date->format('d');

                $encaisse = Cotisation::whereDate('created_at', $date)->sum('montant')
                    + Remboursement::whereDate('created_at', $date)->sum('montant')
                    + FondsDepense::whereDate('created_at', $date)->sum('montant');

                $sorties = Depense::whereDate('created_at', $date)->where('statut', 'validee')->sum('montant')
                    + Pret::whereDate('updated_at', $date)->where('statut', 'valide')->sum('montant_demande');

                $dataEncaisse[] = (float)$encaisse;
                $dataDepenses[] = (float)$sorties;

                $soldeCourant += ($encaisse - $sorties);
                $dataBanque[] = (float)$soldeCourant;
            }
        }

        return response()->json([
            'succes' => true,
            'labels' => $labels,
            'encaisse' => $dataEncaisse,
            'depenses' => $dataDepenses,
            'banque' => $dataBanque,
        ]);
    }

    private function calculerSoldeA($date): float
    {
        $encaisse = Cotisation::where('created_at', '<=', $date)->sum('montant')
            + Remboursement::where('created_at', '<=', $date)->sum('montant')
            + FondsDepense::where('created_at', '<=', $date)->sum('montant');

        $sorties = Depense::where('created_at', '<=', $date)->where('statut', 'validee')->sum('montant')
            + Pret::where('updated_at', '<=', $date)->where('statut', 'valide')->sum('montant_demande');

        return (float)($encaisse - $sorties);
    }
}
