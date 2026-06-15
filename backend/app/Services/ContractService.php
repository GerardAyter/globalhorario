<?php

namespace App\Services;

use App\Models\Contract;

class ContractService extends BaseService
{
    /**
     * List contracts with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return Contract::query()->paginate(20);
    }

    /**
     * Find a contract by id.
     *
     * @param int $id
     * @return Contract|null
     */
    public function find(int $id): ?Contract
    {
        return Contract::find($id);
    }

    /**
     * Create a new contract.
     *
     * @param array $data
     * @return Contract
     */
    public function create(array $data): Contract
    {
        return Contract::create($data);
    }

    /**
     * Update a contract.
     *
     * @param Contract $item
     * @param array $data
     * @return Contract
     */
    public function update(Contract $item, array $data): Contract
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete a contract.
     *
     * @param Contract $item
     * @return bool
     */
    public function delete(Contract $item): bool
    {
        return $item->delete();
    }
}
