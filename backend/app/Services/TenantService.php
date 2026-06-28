<?php

namespace App\Services;

use App\Models\Tenant;

class TenantService extends BaseService
{
    /**
     * List tenants.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = []) // $filters reserved for future use
    {
        return Tenant::query()
            ->with([
                'whitelabel:id,tenant_id,nom_producte,logo_url,favicon_url',
                'planFeatureFlags' => fn ($q) => $q->where('actiu', true)->select('id', 'tenant_id', 'feature'),
            ])
            ->withCount('companies')
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Find a tenant by id.
     *
     * @param int $id
     * @return Tenant|null
     */
    public function find(int $id): ?Tenant
    {
        return Tenant::find($id);
    }

    public function findWithRelations(int $id): ?Tenant
    {
        return Tenant::with([
            'whitelabel:id,tenant_id,nom_producte,logo_url,favicon_url',
            'planFeatureFlags' => fn ($q) => $q->select('id', 'tenant_id', 'feature', 'actiu'),
        ])
        ->withCount('companies')
        ->find($id);
    }

    /**
     * Create a tenant.
     *
     * @param array $data
     * @return Tenant
     */
    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    /**
     * Update a tenant.
     *
     * @param Tenant $tenant
     * @param array $data
     * @return Tenant
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);
        return $tenant;
    }

    /**
     * Delete a tenant.
     *
     * @param Tenant $tenant
     * @return bool
     */
    public function delete(Tenant $tenant): bool
    {
        return $tenant->delete();
    }
}
