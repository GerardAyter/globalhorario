<?php

namespace App\Services;

use App\Models\AbsenceType;

class AbsenceTypeService extends BaseService
{
    /**
     * List absence types with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return AbsenceType::query()->paginate(20);
    }

    /**
     * Find an absence type by id.
     *
     * @param int $id
     * @return AbsenceType|null
     */
    public function find(int $id): ?AbsenceType
    {
        return AbsenceType::find($id);
    }

    /**
     * Create a new absence type.
     *
     * @param array $data
     * @return AbsenceType
     */
    public function create(array $data): AbsenceType
    {
        return AbsenceType::create($data);
    }

    /**
     * Update an absence type.
     *
     * @param AbsenceType $item
     * @param array $data
     * @return AbsenceType
     */
    public function update(AbsenceType $item, array $data): AbsenceType
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete an absence type.
     *
     * @param AbsenceType $item
     * @return bool
     */
    public function delete(AbsenceType $item): bool
    {
        return $item->delete();
    }
}
