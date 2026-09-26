<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifierTenant
{
    /**
     * Identifie l'organisation / tontine active pour la requête courante.
     *
     * Méthodes de détection (par ordre de priorité) :
     * 1. En-tête HTTP 'X-Tenant-Slug' ou 'X-Tenant-Id'
     * 2. Tenant associé à l'utilisateur connecté ($user->tenant_id)
     * 3. Sous-domaine de la requête (ex: asso-paris.tontinepro.com)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // 1. En-tête HTTP (idéal pour le frontend React SPA)
        if ($request->hasHeader('X-Tenant-Slug')) {
            $slug = $request->header('X-Tenant-Slug');
            $tenant = Tenant::where('slug', $slug)->first();
        } elseif ($request->hasHeader('X-Tenant-Id')) {
            $id = $request->header('X-Tenant-Id');
            $tenant = Tenant::find($id);
        }

        // 2. Utilisateur connecté
        if (!$tenant && $request->user() && $request->user()->tenant_id) {
            $tenant = Tenant::find($request->user()->tenant_id);
        }

        // 3. Détection par sous-domaine si applicable
        if (!$tenant) {
            $host = $request->getHost();
            $parties = explode('.', $host);
            if (count($parties) >= 3 && !in_array($parties[0], ['www', 'api', 'localhost', '127'])) {
                $tenant = Tenant::where('slug', $parties[0])->first();
            }
        }

        // Si un tenant a été trouvé, vérifier son statut
        if ($tenant) {
            if ($tenant->statut !== 'actif') {
                return response()->json([
                    'succes' => false,
                    'message' => "Cette organisation est temporairement suspendue. Veuillez contacter l'administrateur.",
                ], 403);
            }

            // Enregistrement dans le conteneur IoC pour injection et Global Scopes
            app()->instance('tenant_actuel', $tenant);
        }

        return $next($request);
    }
}
