<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait AppartientAuTenant
{
    /**
     * Boot du trait pour injecter le scope global et renseigner automatiquement tenant_id.
     */
    public static function bootAppartientAuTenant(): void
    {
        // Scope global automatique : filtre les enregistrements par le tenant actif
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('tenant_actuel')) {
                $tenant = app('tenant_actuel');
                if ($tenant && !empty($tenant->id)) {
                    $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenant->id);
                }
            }
        });

        // Lors de la création d'un modèle, associer automatiquement le tenant actif
        static::creating(function ($model) {
            if (app()->bound('tenant_actuel')) {
                $tenant = app('tenant_actuel');
                if ($tenant && !empty($tenant->id) && empty($model->tenant_id)) {
                    $model->tenant_id = $tenant->id;
                }
            }
        });
    }

    /**
     * Relation vers l'organisation / tontine parente.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Scope local pour désactiver le filtre tenant (ex: super-admin global).
     */
    public function scopeSansFiltreTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}
