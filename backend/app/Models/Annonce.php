<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AppartientAuTenant;

class Annonce extends Model
{
    use HasFactory, AppartientAuTenant;

    protected $fillable = [
        'tenant_id',
        'titre',
        'message',
        'user_id',
        'target_role', // null = public, 'admin' = admin only, etc.
    ];

    // Relation avec l'auteur de l'annonce
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation many-to-many avec les utilisateurs qui ont lu l'annonce
    public function readers()
    {
        return $this->belongsToMany(User::class, 'annonce_user')->withPivot('read_at');
    }

    // Alias pour la relation readers (utilisé dans certains endroits du code)
    public function users()
    {
        return $this->readers();
    }

    // Petite fonction utilitaire pour savoir si l'user connecté a lu
    public function isReadBy($user)
    {
        return $this->readers->contains($user->id);
    }
}
