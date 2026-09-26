<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnonceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:100',
            'message' => 'required|string|max:500',
        ]);

        Annonce::create([
            'titre' => $request->titre,
            'message' => $request->message,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Annonce publiée sur le tableau de bord.');
    }

    public function destroy(Annonce $annonce)
    {
        $annonce->delete();
        return back()->with('success', 'Annonce supprimée.');
    }

    public function markAsRead($id)
    {
        $annonce = Annonce::findOrFail($id);
        $user = Auth::user();

        // Si l'utilisateur n'a pas encore lu l'annonce, on l'attache à la table pivot
        if (!$annonce->isReadBy($user)) {
            $annonce->readers()->attach($user->id);
        }

        // On retourne le détail de l'annonce (ou une vue partielle) pour le Modal
        // Pour faire simple ici, on redirige vers le dashboard avec un paramètre pour ouvrir le modal
        return redirect()->route('dashboard')->with('open_annonce', $annonce->id);
    }
}
