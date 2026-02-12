<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCycleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Change 'false' en 'true' pour autoriser l'action
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut', // Doit être après le début
            'montant_part' => 'required|numeric|min:0',
            'taux_interet' => 'required|numeric|min:0|max:100',
            'montant_mange_mille' => 'nullable|numeric|min:0',
            'frequence_paiement' => 'required|in:mensuelle,hebdomadaire',
        ];
    }
}
