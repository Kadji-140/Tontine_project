<?php

namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Carbon\Carbon;

class StorePretRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $seanceId = $this->input('seance_id');
        $seance = \App\Models\Seance::with('cycle')->find($seanceId);

        // Calculer la date maximum
        $dateMax3Mois = now()->addMonths(3)->format('Y-m-d');
        $dateFinCycle = $seance ? Carbon::parse($seance->cycle->date_fin)->format('Y-m-d') : $dateMax3Mois;
        $dateMaximum = min($dateMax3Mois, $dateFinCycle);

        // Règle SPÉCIALE :
                // On exige 'user_id' SEULEMENT si ce n'est PAS un membre (donc admin ou trésorier)
                if ($this->user()->role !== 'membre') {
                    $rules['user_id'] = 'required|exists:users,id';
                }
        return [
            'seance_id' => 'required|exists:seances,id',
            'user_id' => 'required_if:role,treasurer|exists:users,id',
            'montant_demande' => 'required|numeric|min:1000',
            'date_echeance' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . $dateMaximum,
            ],
        ];

    }

    public function messages()
    {
        $seanceId = $this->input('seance_id');
        $seance = \App\Models\Seance::with('cycle')->find($seanceId);
        $dateFinCycle = $seance ? Carbon::parse($seance->cycle->date_fin)->format('d/m/Y') : '';

        return [
            'date_echeance.after_or_equal' => 'La date d\'échéance ne peut pas être antérieure à aujourd\'hui.',
            'date_echeance.before_or_equal' => 'La date d\'échéance ne peut pas dépasser 3 mois ou la fin du cycle (' . $dateFinCycle . ').',
        ];
    }
}
