<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSanctionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'montant' => 'required|numeric|min:100', // Ex: 100 FCFA minimum
            'motif' => 'required|string|max:255', // Ex: "Retard de 30min"
        ];
    }
}
