<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AppartientAuTenant;

class Pret extends Model
{
    use HasFactory, AppartientAuTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'seance_id',
        'montant_demande',
        'interet_total',
        'date_echeance',
        'statut', // en_attente, valide, refuse, rembourse
        'date_echeance_modifiee',
        'date_modification_proposee',
        'est_accepte_par_membre',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'est_accepte_par_membre' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }

    public function remboursements()
    {
        return $this->hasMany(Remboursement::class);
    }

    /**
     * Calcule le montant total déjà remboursé
     */
    public function getMontantRembourseAttribute()
    {
        return $this->remboursements()->sum('montant');
    }

    /**
     * Calcule le montant total à rembourser (Capital + Intérêts)
     */
    public function getTotalARembourserAttribute()
    {
        return $this->montant_demande + $this->interet_total;
    }

    /**
     * Calcule le reste à payer (Capital + Intérêts - Déjà payé)
     */
    public function getResteAPayerAttribute()
    {
        return $this->total_a_rembourser - $this->montant_rembourse;
    }
}
