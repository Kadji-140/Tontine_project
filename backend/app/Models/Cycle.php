<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\AppartientAuTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cycle extends Model
{
    use HasFactory, AppartientAuTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'nom',
        'date_debut',
        'date_fin',
        'montant_part',
        'montant_mange_mille',
        'taux_interet',        // Taux Prêt
        'taux_interet_banque', // Taux Banque (Calculé maintenant, mais gardé en DB pour historique ou défaut ?) 
        // Note: Le user a demandé de retirer le champ des formulaires, mais le champ existe en DB.
        // On le garde dans le fillable au cas où.
        'est_actif',
        'frequence_paiement'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'est_actif' => 'boolean',
    ];

    // Un cycle contient plusieurs séances
    public function seances()
    {
        return $this->hasMany(Seance::class);
    }

    // Un cycle a plusieurs membres (avec leur rang de passage)
    public function membres()
    {
        return $this->belongsToMany(User::class, 'cycle_user')
                    ->withPivot('rang')
                    ->withTimestamps()
                    ->orderByPivot('rang', 'asc');
    }
    // Un cycle a plusieurs paiements de lots
    public function paiements()
    {
        return $this->hasMany(PaiementLot::class);
    }
}
