<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AppartientAuTenant;

class PaiementLot extends Model
{
    use HasFactory, AppartientAuTenant;

    protected $table = 'paiements_lots';

    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'user_id',
        'seance_id',
        'montant',
        'date_paiement',
        'statut', // en_attente, confirme, rejete
    ];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }
}
