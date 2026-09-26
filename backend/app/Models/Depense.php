<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AppartientAuTenant;

class Depense extends Model
{
    use HasFactory, AppartientAuTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'seance_id',
        'enregistre_par',
        'motif',
        'montant',
        'statut',
        'justificatif',
    ];

    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }
}
