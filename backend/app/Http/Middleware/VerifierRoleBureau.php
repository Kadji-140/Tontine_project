<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerifierRoleBureau
{
    /**
     * Vérifie que l'utilisateur connecté possède le rôle admin ou trésorier.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
            return redirect('/login');
        }

        $utilisateur = Auth::user();

        if ($utilisateur->role === 'admin' || $utilisateur->role === 'tresorier') {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Accès refusé : cette action est réservée aux membres du bureau.'], 403);
        }

        abort(403, "ACCÈS REFUSÉ : Vous n'avez pas les droits de bureau.");
    }
}
