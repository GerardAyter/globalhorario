<?php

namespace App\Services;

use App\Models\PolicyAbsence;

class PolicyAbsenceService extends BaseService
{
    /**
     * List policy absences.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return PolicyAbsence::query()->paginate(20);
    }

    /**
     * Find a policy absence by id.
     *
     * @param int $id
     * @return PolicyAbsence|null
     */
    public function find(int $id): ?PolicyAbsence
    {
        return PolicyAbsence::find($id);
    }

    /**
     * Create a policy absence.
     *
     * @param array $data
     * @return PolicyAbsence
     */
    public function create(array $data): PolicyAbsence
    {
        return PolicyAbsence::create($data);
    }

    /**
     * Update a policy absence.
     *
     * @param PolicyAbsence $item
     * @param array $data
     * @return PolicyAbsence
     */
    public function update(PolicyAbsence $item, array $data): PolicyAbsence
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete a policy absence.
     *
     * @param PolicyAbsence $item
     * @return bool
     */
    public function delete(PolicyAbsence $item): bool
    {
        return $item->delete();
    }
}
