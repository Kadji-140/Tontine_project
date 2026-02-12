<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // <-- Mets à true
    }

    public function rules(): array
    {
        return [
            'cycle_id' => 'required|exists:cycles,id', // Le cycle doit exister en BD
            'date_seance' => [
                'required',
                'date',
                'after_or_equal:' . now()->subDays(2)->toDateString(), // Max 2 jours en arrière
            ],
            'statut' => 'required|in:ouverte,fermee',
        ];
    }

    public function messages(): array
    {
        return [
            'date_seance.after_or_equal' => 'La date de la séance ne peut pas être antérieure de plus de 2 jours.',
        ];
    }
}
