<?php

namespace App\Services;

use App\Models\VacationBalance;

class VacationBalanceService extends BaseService
{
    /**
     * List vacation balances.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return VacationBalance::query()->paginate(20);
    }

    /**
     * Find a vacation balance by id.
     *
     * @param int $id
     * @return VacationBalance|null
     */
    public function find(int $id): ?VacationBalance
    {
        return VacationBalance::find($id);
    }

    /**
     * Create a vacation balance.
     *
     * @param array $data
     * @return VacationBalance
     */
    public function create(array $data): VacationBalance
    {
        return VacationBalance::create($data);
    }

    /**
     * Update a vacation balance.
     *
     * @param VacationBalance $item
     * @param array $data
     * @return VacationBalance
     */
    public function update(VacationBalance $item, array $data): VacationBalance
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete a vacation balance.
     *
     * @param VacationBalance $item
     * @return bool
     */
    public function delete(VacationBalance $item): bool
    {
        return $item->delete();
    }
}
