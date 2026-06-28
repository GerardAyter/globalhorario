<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Trait HasTenant
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasTenant
{
    public static function bootHasTenant(): void
    {
        $class = static::class;
        if (is_subclass_of($class, EloquentModel::class)) {
            call_user_func([$class, 'addGlobalScope'], new TenantScope());

            call_user_func([$class, 'creating'], function ($model) {
                $tenantId = TenantContext::getTenantId();
                if ($tenantId && empty($model->tenant_id)) {
                    $model->tenant_id = $tenantId;
                }
            });
        }
    }
}
