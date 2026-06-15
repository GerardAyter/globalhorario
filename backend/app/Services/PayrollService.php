<?php

namespace App\Services;

use App\Models\Payroll;

class PayrollService extends BaseService
{
    /**
     * List payrolls with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return Payroll::query()->paginate(20);
    }

    /**
     * Find a payroll by id.
     *
     * @param int $id
     * @return Payroll|null
     */
    public function find(int $id): ?Payroll
    {
        return Payroll::find($id);
    }

    /**
     * Create a new payroll.
     *
     * @param array $data
     * @return Payroll
     */
    public function create(array $data): Payroll
    {
        return Payroll::create($data);
    }

    /**
     * Update an existing payroll.
     *
     * @param Payroll $item
     * @param array $data
     * @return Payroll
     */
    public function update(Payroll $item, array $data): Payroll
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete a payroll record.
     *
     * @param Payroll $item
     * @return bool
     */
    public function delete(Payroll $item): bool
    {
        return $item->delete();
    }
}
