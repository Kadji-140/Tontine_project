<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Dans UserController.php
    // app/Http/Controllers/UserController.php
    public function getPretStats(User $user)
    {
        $stats = [
            'total_prets' => $user->prets()->count(),
            'prets_en_attente' => $user->prets()->where('statut', 'en_attente')->count(),
            'prets_valides' => $user->prets()->where('statut', 'valide')->count(),
            'prets_rembourses' => $user->prets()->where('statut', 'rembourse')->count(),
            'montant_total_emprunte' => $user->prets()->where('statut', '!=', 'en_attente')->sum('montant_demande'),
            'montant_total_interets' => $user->prets()->where('statut', '!=', 'en_attente')->sum('interet_total'),
            'prets_en_retard' => $user->prets()
                ->where('statut', 'valide')
                ->where('date_echeance', '<', now())
                ->count(),
        ];

        return response()->json($stats);
    }

    public function getDetailsMembre(User $user)
    {
        // Sécurité supplémentaire (en plus du middleware)
        if (auth()->user()->role === 'membre' && auth()->id() !== $user->id) {
            abort(403);
        }

        // 1. Informations de base (déjà dans $user)

        // 2. Statistiques (Same as getPretStats)
        $stats = [
            'total_prets' => $user->prets()->count(),
            'prets_en_attente' => $user->prets()->where('statut', 'en_attente')->count(),
            'prets_valides' => $user->prets()->where('statut', 'valide')->count(),
            'prets_rembourses' => $user->prets()->where('statut', 'rembourse')->count(),
            'montant_total_emprunte' => $user->prets()->where('statut', '!=', 'en_attente')->sum('montant_demande'),
            'prets_en_retard' => $user->prets()
                ->where('statut', 'valide')
                ->where('date_echeance', '<', now())
                ->count(),
        ];

        // 3. Les 5 derniers prêts
        $recent_prets = $user->prets()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($pret) {
                return [
                    'montant' => number_format($pret->montant_demande, 0, ',', ' '),
                    'interet' => number_format($pret->interet_total, 0, ',', ' '),
                    'date_echeance' => $pret->date_echeance->format('d/m/Y'),
                    'statut' => $pret->statut,
                    'created_at' => $pret->created_at->format('d/m/Y'),
                    'est_accepte_par_membre' => $pret->est_accepte_par_membre,
                    'date_echeance_modifiee' => $pret->date_echeance_modifiee,
                ];
            });

        return response()->json([
            'user' => $user,
            'stats' => $stats,
            'recent_prets' => $recent_prets
        ]);
    }
    public function index()
    {
        // Seuls les admins / trésoriers peuvent voir la liste
        if (auth()->user()->role === 'membre') {
            abort(403);
        }

        $users = User::orderBy('created_at', 'desc')->get();
        return view('users.index', compact('users'));
    }

    public function toggleStatus(User $user)
    {
        if (auth()->user()->role === 'membre') {
            abort(403);
        }
        
        // Empêcher de se désactiver soi-même
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Le compte de {$user->name} a été {$status}.");
    }

    public function destroy(User $user)
    {
        if (auth()->user()->role === 'membre') {
            abort(403);
        }

        // Empêcher de supprimer son propre compte ou un compte déjà actif (sécurité supplémentaire)
        // Le besoin specifie "annuler la candidature", donc implicitement des inactifs.
        // Mais l'admin peut vouloir supprimer un membre actif aussi. Restons flexibles mais prudents.
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete(); // Soft delete si activé, sinon delete définitif

        return back()->with('success', "Le dossier de {$user->name} a été supprimé.");
    }
}
