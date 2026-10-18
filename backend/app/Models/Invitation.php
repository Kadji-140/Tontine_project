<?php

namespace App\Models;

use App\Traits\AppartientAuTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    use HasFactory, AppartientAuTenant;

    protected $fillable = [
        'tenant_id',
        'token',
        'email',
        'role',
        'max_utilisations',
        'nb_utilisations',
        'expire_le',
        'cree_par',
    ];

    protected $casts = [
        'expire_le' => 'datetime',
        'max_utilisations' => 'integer',
        'nb_utilisations' => 'integer',
    ];

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    /**
     * Vérifie si l'invitation est encore valide.
     */
    public function estValide(): bool
    {
        return $this->expire_le->isFuture() && ($this->nb_utilisations < $this->max_utilisations);
    }
}
