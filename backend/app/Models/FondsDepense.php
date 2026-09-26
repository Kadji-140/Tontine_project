<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AppartientAuTenant;

class FondsDepense extends Model
{
    use HasFactory, AppartientAuTenant;

    protected $table = 'fonds_depenses';

    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'seance_id',
        'user_id',
        'type', // 'sanction', 'inscription', 'secours', 'mange_mille', 'autre'
        'montant',
        'description',
    ];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
