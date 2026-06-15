<?php

namespace App\Services;

use App\Models\Overtime;

class OvertimeService extends BaseService
{
    /**
     * List overtimes with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return Overtime::query()->paginate(20);
    }

    /**
     * Find an overtime record by id.
     *
     * @param int $id
     * @return Overtime|null
     */
    public function find(int $id): ?Overtime
    {
        return Overtime::find($id);
    }

    /**
     * Create an overtime record.
     *
     * @param array $data
     * @return Overtime
     */
    public function create(array $data): Overtime
    {
        return Overtime::create($data);
    }

    /**
     * Update an overtime record.
     *
     * @param Overtime $item
     * @param array $data
     * @return Overtime
     */
    public function update(Overtime $item, array $data): Overtime
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete an overtime record.
     *
     * @param Overtime $item
     * @return bool
     */
    public function delete(Overtime $item): bool
    {
        return $item->delete();
    }

    /**
     * Approve an overtime record.
     *
     * @param Overtime $item
     * @return Overtime
     */
    public function approve(Overtime $item): Overtime
    {
        $item->status = Overtime::STATUS_APPROVED;
        $item->save();
        return $item;
    }
}
