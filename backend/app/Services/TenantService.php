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
    public function list(array $filters = [])
    {
        return Tenant::query()->paginate(20);
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
