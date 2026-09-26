<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifierTenant
{
    /**
     * Identifie et sécurise l'organisation (Tenant) pour la requête en cours.
     *
     * Règles de sécurité strictes :
     * 1. Super-Admin :
     *    - Peut cibler un tenant via 'X-Tenant-Slug' ou 'X-Tenant-Id' (mode inspection).
     *    - Sans en-tête, aucun tenant n'est imposé (vue globale transversale).
     * 2. Utilisateurs réguliers (Admin, Trésorier, Membre) :
     *    - Les en-têtes sont ignorés pour éviter toute usurpation (anti-spoofing).
     *    - Le tenant est déterminé EXCLUSIVEMENT par $user->tenant_id.
     *    - Si l'organisation est suspendue -> rejet 403 immédiat.
     * 3. Invités (non connectés) :
     *    - Détection via en-tête ou sous-domaine (ex: page d'inscription dédiée à une tontine).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;
        $user = $request->user();

        // 1. CAS UTILISATEUR CONNECTÉ
        if ($user) {
            if ($user->est_super_admin) {
                // Le Super-Admin peut choisir d'inspecter un tenant spécifique via header
                if ($request->hasHeader('X-Tenant-Slug')) {
                    $tenant = Tenant::where('slug', $request->header('X-Tenant-Slug'))->first();
                } elseif ($request->hasHeader('X-Tenant-Id')) {
                    $tenant = Tenant::find($request->header('X-Tenant-Id'));
                }

                // Si un tenant a été explicitement demandé par le Super-Admin, on le lie
                if ($tenant) {
                    app()->instance('tenant_actuel', $tenant);
                }
                // Si aucun tenant n'est demandé, tenant_actuel n'est pas lié -> requêtes globales
                return $next($request);
            }

            // Pour tout utilisateur non super-admin : étanchéité absolue
            if (!$user->tenant_id) {
                return response()->json([
                    'succes' => false,
                    'message' => "Aucune tontine n'est associée à votre compte.",
                ], 403);
            }

            $tenant = Tenant::find($user->tenant_id);

            if (!$tenant) {
                return response()->json([
                    'succes' => false,
                    'message' => "L'organisation associée à ce compte est introuvable.",
                ], 404);
            }

            if ($tenant->statut !== 'actif') {
                return response()->json([
                    'succes' => false,
                    'statut' => 'suspendu',
                    'message' => "Cette tontine est temporairement suspendue par l'administration. Veuillez contacter le support.",
                ], 403);
            }

            // Liaison obligatoire du tenant actif
            app()->instance('tenant_actuel', $tenant);
            return $next($request);
        }

        // 2. CAS INVITÉ (ex: inscription sous un slug spécifique)
        if ($request->hasHeader('X-Tenant-Slug')) {
            $tenant = Tenant::where('slug', $request->header('X-Tenant-Slug'))->first();
        } elseif ($request->hasHeader('X-Tenant-Id')) {
            $tenant = Tenant::find($request->header('X-Tenant-Id'));
        }

        if ($tenant && $tenant->statut === 'actif') {
            app()->instance('tenant_actuel', $tenant);
        }

        return $next($request);
    }
}
