<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authentification de l'utilisateur (Connexion).
     */
    public function connexion(Request $request): JsonResponse
    {
        $champsValides = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $utilisateur = User::where('email', $champsValides['email'])->first();

        if (!$utilisateur || !Hash::check($champsValides['password'], $utilisateur->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        Auth::login($utilisateur, $request->boolean('remember'));

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        // Création du token Sanctum pour le client API
        $jeton = $utilisateur->createToken('auth_token')->plainTextToken;

        return response()->json([
            'succes' => true,
            'message' => 'Connexion réussie.',
            'utilisateur' => $utilisateur->load('tenant'),
            'jeton' => $jeton,
            'est_actif' => (bool)$utilisateur->is_active,
            'est_bureau' => in_array($utilisateur->role, ['admin', 'tresorier']),
            'est_super_admin' => (bool)$utilisateur->est_super_admin,
        ]);
    }

    /**
     * Liste publique des organisations tontines actives pour sélection lors de l'adhésion.
     */
    public function tontinesPubliques(): JsonResponse
    {
        $tontines = Tenant::where('statut', 'actif')
            ->select('id', 'nom', 'slug', 'devise')
            ->orderBy('nom', 'asc')
            ->get();

        return response()->json([
            'succes' => true,
            'tontines' => $tontines,
        ]);
    }

    /**
     * Enregistrement d'un nouveau membre (Inscription).
     */
    public function inscription(Request $request): JsonResponse
    {
        $champsValides = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', 'max:20', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'tenant_id' => ['nullable', 'integer'],
            'tenant_slug' => ['nullable', 'string'],
            'creer_nouvelle_tontine' => ['nullable', 'boolean'],
            'nom_nouvelle_tontine' => ['nullable', 'string', 'max:255'],
        ]);

        $role = 'membre';
        $estActif = false;
        $tenantId = null;

        if ($request->boolean('creer_nouvelle_tontine') && $request->filled('nom_nouvelle_tontine')) {
            // Mode Fondateur : Création d'une nouvelle tontine sur la plateforme
            $nomTontine = trim($request->string('nom_nouvelle_tontine'));
            $baseSlug = Str::slug($nomTontine);
            $slug = $baseSlug ?: 'tontine';
            if (Tenant::where('slug', $slug)->exists()) {
                $slug .= '-' . rand(100, 999);
            }

            $nouveauTenant = Tenant::create([
                'nom' => $nomTontine,
                'slug' => $slug,
                'devise' => 'FCFA',
                'statut' => 'actif',
            ]);

            $tenantId = $nouveauTenant->id;
            $role = 'admin'; // Le créateur est désigné Président / Admin
            $estActif = true; // Actif immédiatement
        } else {
            // Mode Adhérent : Rejoindre une tontine existante
            if ($request->filled('tenant_id')) {
                $t = Tenant::where('statut', 'actif')->find($request->integer('tenant_id'));
                if ($t) $tenantId = $t->id;
            } elseif ($request->filled('tenant_slug')) {
                $t = Tenant::where('statut', 'actif')->where('slug', $request->string('tenant_slug'))->first();
                if ($t) $tenantId = $t->id;
            } elseif (app()->bound('tenant_actuel')) {
                $tenantId = app('tenant_actuel')?->id;
            }

            // Si aucune tontine n'a été spécifiée, rattacher automatiquement à la première active
            if (!$tenantId) {
                $premierTenant = Tenant::where('statut', 'actif')->first();
                $tenantId = $premierTenant?->id;
            }

            $role = 'membre';
            $estActif = false; // Membre en sas d'attente d'approbation par le bureau
        }

        $utilisateur = User::create([
            'tenant_id' => $tenantId,
            'name' => $champsValides['name'],
            'email' => $champsValides['email'],
            'phone' => $champsValides['phone'],
            'password' => Hash::make($champsValides['password']),
            'role' => $role,
            'est_super_admin' => false,
            'status' => 'actif',
            'is_active' => $estActif,
        ]);

        Auth::login($utilisateur);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $jeton = $utilisateur->createToken('auth_token')->plainTextToken;

        $message = $estActif
            ? "Félicitations ! Votre organisation tontinière a été créée avec succès. Vous êtes connecté en tant que Président."
            : "Inscription effectuée avec succès. Votre compte est en attente d'activation par le bureau de votre tontine.";

        return response()->json([
            'succes' => true,
            'message' => $message,
            'utilisateur' => $utilisateur->load('tenant'),
            'jeton' => $jeton,
            'est_actif' => $estActif,
            'est_bureau' => in_array($role, ['admin', 'tresorier']),
            'est_super_admin' => false,
        ], 201);
    }

    /**
     * Retourne les données de l'utilisateur connecté.
     */
    public function utilisateurActuel(Request $request): JsonResponse
    {
        $utilisateur = $request->user()->load('tenant');

        return response()->json([
            'succes' => true,
            'utilisateur' => $utilisateur,
            'est_actif' => (bool)$utilisateur->is_active,
            'est_bureau' => in_array($utilisateur->role, ['admin', 'tresorier']),
            'est_super_admin' => (bool)$utilisateur->est_super_admin,
        ]);
    }

    /**
     * Déconnexion de l'utilisateur.
     */
    public function deconnexion(Request $request): JsonResponse
    {
        if (Auth::check()) {
            $utilisateur = Auth::user();
            // Révoquer les tokens Sanctum actuels si présents
            $utilisateur->tokens()->delete();
            Auth::guard('web')->logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'succes' => true,
            'message' => 'Déconnexion effectuée avec succès.',
        ]);
    }
}
