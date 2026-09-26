<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCotisationRequest extends FormRequest
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
            'seance_id' => 'required|exists:seances,id',
            'user_id' => 'required|exists:users,id',
            'montant' => 'required|numeric|min:500', // Ex: Minimum 500 FCFA
            'type' => 'required|in:tontine,secours,banque',
        ];
    }
}
