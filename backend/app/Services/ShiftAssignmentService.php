<?php

namespace App\Services;

use App\Models\ShiftAssignment;

class ShiftAssignmentService extends BaseService
{
    /**
     * List shift assignments with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return ShiftAssignment::query()->paginate(20);
    }

    /**
     * Find a shift assignment by id.
     *
     * @param int $id
     * @return ShiftAssignment|null
     */
    public function find(int $id): ?ShiftAssignment
    {
        return ShiftAssignment::find($id);
    }

    /**
     * Create a new shift assignment.
     *
     * @param array $data
     * @return ShiftAssignment
     */
    public function create(array $data): ShiftAssignment
    {
        return ShiftAssignment::create($data);
    }

    /**
     * Update an existing shift assignment.
     *
     * @param ShiftAssignment $item
     * @param array $data
     * @return ShiftAssignment
     */
    public function update(ShiftAssignment $item, array $data): ShiftAssignment
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete a shift assignment.
     *
     * @param ShiftAssignment $item
     * @return bool
     */
    public function delete(ShiftAssignment $item): bool
    {
        return $item->delete();
    }
}
