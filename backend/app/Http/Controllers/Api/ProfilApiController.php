<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfilApiController extends Controller
{
    /**
     * Données du profil de l'utilisateur connecté.
     */
    public function afficher(Request $request): JsonResponse
    {
        return response()->json([
            'succes' => true,
            'utilisateur' => $request->user(),
        ]);
    }

    /**
     * Mise à jour des informations de profil.
     */
    public function mettreAJour(Request $request): JsonResponse
    {
        $utilisateur = $request->user();

        $valide = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($utilisateur->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($utilisateur->id)],
            'profession' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'cni' => ['nullable', 'string', 'max:50'],
            'beneficiaire' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($utilisateur->avatar) {
                Storage::disk('public')->delete($utilisateur->avatar);
            }
            $chemin = $request->file('avatar')->store('avatars', 'public');
            $valide['avatar'] = $chemin;
        }

        $utilisateur->fill($valide);
        $utilisateur->save();

        return response()->json([
            'succes' => true,
            'message' => 'Profil mis à jour avec succès.',
            'utilisateur' => $utilisateur->fresh(),
        ]);
    }

    /**
     * Modification du mot de passe.
     */
    public function changerMotDePasse(Request $request): JsonResponse
    {
        $valide = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($valide['password']),
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Mot de passe modifié avec succès.',
        ]);
    }

    /**
     * Suppression / Désactivation du compte.
     */
    public function supprimer(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $utilisateur = $request->user();

        Auth::logout();
        $utilisateur->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'succes' => true,
            'message' => 'Votre compte a été supprimé.',
        ]);
    }
}
