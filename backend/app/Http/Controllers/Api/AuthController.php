<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
     * Enregistrement d'un nouveau membre (Inscription).
     */
    public function inscription(Request $request): JsonResponse
    {
        $champsValides = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', 'max:20', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $tenantId = null;
        if (app()->bound('tenant_actuel')) {
            $tenantId = app('tenant_actuel')?->id;
        }

        $utilisateur = User::create([
            'tenant_id' => $tenantId,
            'name' => $champsValides['name'],
            'email' => $champsValides['email'],
            'phone' => $champsValides['phone'],
            'password' => Hash::make($champsValides['password']),
            'role' => 'membre',
            'est_super_admin' => false,
            'status' => 'actif',
            'is_active' => false, // Doit être validé par un administrateur du tenant
        ]);

        Auth::login($utilisateur);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $jeton = $utilisateur->createToken('auth_token')->plainTextToken;

        return response()->json([
            'succes' => true,
            'message' => "Inscription effectuée avec succès. Votre compte est en attente d'activation par le bureau.",
            'utilisateur' => $utilisateur->load('tenant'),
            'jeton' => $jeton,
            'est_actif' => false,
            'est_bureau' => false,
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
