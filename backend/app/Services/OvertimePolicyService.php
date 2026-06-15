<?php

namespace App\Services;

use App\Models\OvertimePolicy;

class OvertimePolicyService extends BaseService
{
    /**
     * List overtime policies.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return OvertimePolicy::query()->paginate(20);
    }

    /**
     * Find an overtime policy by id.
     *
     * @param int $id
     * @return OvertimePolicy|null
     */
    public function find(int $id): ?OvertimePolicy
    {
        return OvertimePolicy::find($id);
    }

    /**
     * Create a new overtime policy.
     *
     * @param array $data
     * @return OvertimePolicy
     */
    public function create(array $data): OvertimePolicy
    {
        return OvertimePolicy::create($data);
    }

    /**
     * Update an overtime policy.
     *
     * @param OvertimePolicy $item
     * @param array $data
     * @return OvertimePolicy
     */
    public function update(OvertimePolicy $item, array $data): OvertimePolicy
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete an overtime policy.
     *
     * @param OvertimePolicy $item
     * @return bool
     */
    public function delete(OvertimePolicy $item): bool
    {
        return $item->delete();
    }
}
