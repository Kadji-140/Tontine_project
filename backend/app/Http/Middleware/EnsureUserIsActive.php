<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check() && !\Illuminate\Support\Facades\Auth::user()->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'statut' => 'inactif',
                    'message' => "Votre compte est en attente d'activation par le bureau."
                ], 403);
            }

            // Si l'utilisateur est inactif, on le redirige vers la page d'attente
            // SAUF s'il est déjà sur cette page ou s'il se déconnecte (sinon boucle infinie)
            if (!$request->routeIs('inactive') && !$request->routeIs('logout')) {
                return redirect()->route('inactive');
            }
        }
        
        // Si l'utilisateur est INACTIF mais essaie d'aller sur une autre page que 'inactive' (sauf logout), le bloc ci-dessus gère.
        // Si l'utilisateur est ACTIF et essaie d'aller sur 'inactive', on devrait peut-être le rediriger vers dashboard ?
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->is_active && $request->routeIs('inactive')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
