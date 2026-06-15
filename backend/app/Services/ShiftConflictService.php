<?php

namespace App\Services;

use App\Models\ShiftConflict;

class ShiftConflictService extends BaseService
{
    /**
     * List shift conflicts with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return ShiftConflict::query()->paginate(20);
    }

    /**
     * Find a shift conflict by id.
     *
     * @param int $id
     * @return ShiftConflict|null
     */
    public function find(int $id): ?ShiftConflict
    {
        return ShiftConflict::find($id);
    }

    /**
     * Create a new shift conflict record.
     *
     * @param array $data
     * @return ShiftConflict
     */
    public function create(array $data): ShiftConflict
    {
        return ShiftConflict::create($data);
    }

    /**
     * Update an existing shift conflict.
     *
     * @param ShiftConflict $item
     * @param array $data
     * @return ShiftConflict
     */
    public function update(ShiftConflict $item, array $data): ShiftConflict
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete a shift conflict.
     *
     * @param ShiftConflict $item
     * @return bool
     */
    public function delete(ShiftConflict $item): bool
    {
        return $item->delete();
    }

    /**
     * Resolve a shift conflict with resolution data.
     *
     * @param ShiftConflict $item
     * @param array $data
     * @return ShiftConflict
     */
    public function resolve(ShiftConflict $item, array $data = []): ShiftConflict
    {
        $item->resolution = $data['resolution'] ?? 'resolved';
        $item->resolution_note = $data['resolution_note'] ?? null;
        $item->resolved_at = now();
        $item->save();
        return $item;
    }
}
