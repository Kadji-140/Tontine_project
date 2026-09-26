<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FondsDepense extends Model
{
    protected $fillable = [
        'cycle_id',
        'seance_id',
        'user_id',
        'type', // 'sanction', 'inscription', 'secours', 'mange_mille', 'autre'
        'montant',
        'description'
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
