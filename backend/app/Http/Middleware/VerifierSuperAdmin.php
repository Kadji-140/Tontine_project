<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifierSuperAdmin
{
    /**
     * Protège les routes de gestion SaaS de niveau plateforme.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->est_super_admin) {
            return response()->json([
                'succes' => false,
                'message' => "Accès non autorisé : Cette section est strictement réservée au Super-Administrateur de la plateforme.",
            ], 403);
        }

        return $next($request);
    }
}
