<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AppartientAuTenant;

class Remboursement extends Model
{
    use HasFactory, AppartientAuTenant;

    protected $fillable = [
        'tenant_id',
        'pret_id',
        'seance_id',
        'user_id',
        'enregistre_par',
        'montant',
    ];

    public function pret()
    {
        return $this->belongsTo(Pret::class);
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
