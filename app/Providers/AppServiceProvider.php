<?php

namespace App\Providers;

// --- LES IMPORTS DOIVENT ÊTRE ICI (EN HAUT) ---
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;       // <-- Pour gérer la vue
use Illuminate\Support\Facades\Auth;       // <-- Pour gérer l'utilisateur connecté
use App\Models\Annonce;                    // <-- Ton modèle Annonce
// ----------------------------------------------

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // On utilise le View Composer pour injecter les notifications sur TOUTES les pages
        // L'étoile '*' signifie : "Sur toutes les vues"
        View::composer('*', function ($view) {

            // On ne charge les notifs que si quelqu'un est connecté
            if (Auth::check()) {
                $user = Auth::user();

                // 1. Récupérer les 5 dernières annonces CIBLÉES
                $globalAnnonces = Annonce::where(function($query) use ($user) {
                    $query->whereNull('target_role')
                          ->orWhere('target_role', $user->role);
                })->latest()->take(5)->get();

                // 2. Compter combien ne sont PAS lues par l'utilisateur connecté
                $unreadCount = $globalAnnonces->filter(function($annonce) use ($user) {
                    return !$annonce->isReadBy($user);
                })->count();

                // 3. Compter les utilisateurs en attente de validation (is_active = false)
                // Uniquement si l'utilisateur est admin ou trésorier
                $pendingUsersCount = 0;
                if (in_array($user->role, ['admin', 'tresorier'])) {
                    $pendingUsersCount = \App\Models\User::where('is_active', false)->count();
                }

                // 4. Envoyer les variables à la vue
                $view->with('globalAnnonces', $globalAnnonces)
                    ->with('unreadCount', $unreadCount)
                    ->with('pendingUsersCount', $pendingUsersCount);
            }
        });
    }
}
