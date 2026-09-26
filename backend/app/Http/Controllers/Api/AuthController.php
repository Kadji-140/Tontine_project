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

        if (!Auth::attempt($champsValides, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();
        $utilisateur = Auth::user();

        // Création optionnelle d'un token Sanctum pour clients API directs
        $jeton = $utilisateur->createToken('auth_token')->plainTextToken;

        return response()->json([
            'succes' => true,
            'message' => 'Connexion réussie.',
            'utilisateur' => $utilisateur,
            'jeton' => $jeton,
            'est_actif' => (bool)$utilisateur->is_active,
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

        $utilisateur = User::create([
            'name' => $champsValides['name'],
            'email' => $champsValides['email'],
            'phone' => $champsValides['phone'],
            'password' => Hash::make($champsValides['password']),
            'role' => 'membre',
            'status' => 'actif',
            'is_active' => false, // Doit être validé par un administrateur ou trésorier
        ]);

        Auth::login($utilisateur);
        $request->session()->regenerate();

        $jeton = $utilisateur->createToken('auth_token')->plainTextToken;

        return response()->json([
            'succes' => true,
            'message' => "Inscription effectuée avec succès. Votre compte est en attente d'activation par le bureau.",
            'utilisateur' => $utilisateur,
            'jeton' => $jeton,
            'est_actif' => false,
        ], 201);
    }

    /**
     * Retourne les données de l'utilisateur connecté.
     */
    public function utilisateurActuel(Request $request): JsonResponse
    {
        $utilisateur = $request->user();

        return response()->json([
            'succes' => true,
            'utilisateur' => $utilisateur,
            'est_actif' => (bool)$utilisateur->is_active,
            'est_bureau' => in_array($utilisateur->role, ['admin', 'tresorier']),
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

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'succes' => true,
            'message' => 'Déconnexion effectuée avec succès.',
        ]);
    }
}
