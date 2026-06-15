<?php

namespace App\Services;

use App\Models\Shift;

class ShiftService extends BaseService
{
    /**
     * List shifts.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return Shift::query()->paginate(20);
    }

    /**
     * Find a shift by id.
     *
     * @param int $id
     * @return Shift|null
     */
    public function find(int $id): ?Shift
    {
        return Shift::find($id);
    }

    /**
     * Create a new shift.
     *
     * @param array $data
     * @return Shift
     */
    public function create(array $data): Shift
    {
        return Shift::create($data);
    }

    /**
     * Update a shift.
     *
     * @param Shift $shift
     * @param array $data
     * @return Shift
     */
    public function update(Shift $shift, array $data): Shift
    {
        $shift->update($data);
        return $shift;
    }

    /**
     * Delete a shift.
     *
     * @param Shift $shift
     * @return bool
     */
    public function delete(Shift $shift): bool
    {
        return $shift->delete();
    }
}
