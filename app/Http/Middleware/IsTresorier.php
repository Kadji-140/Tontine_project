<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsTresorier
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Si l'utilisateur est Admin OU Trésorier, on laisse passer
        if ($user->role === 'admin' || $user->role === 'tresorier') {
            return $next($request);
        }

        // Sinon, erreur 403 (Interdit)
        abort(403, "ACCÈS REFUSÉ : Vous n'avez pas les droits de trésorerie.");
    }
}
