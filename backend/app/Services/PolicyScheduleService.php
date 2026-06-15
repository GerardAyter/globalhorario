<?php

namespace App\Services;

use App\Models\PolicySchedule;

class PolicyScheduleService extends BaseService
{
    /**
     * List policy schedules.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return PolicySchedule::query()->paginate(20);
    }

    /**
     * Find a policy schedule by id.
     *
     * @param int $id
     * @return PolicySchedule|null
     */
    public function find(int $id): ?PolicySchedule
    {
        return PolicySchedule::find($id);
    }

    /**
     * Create a policy schedule.
     *
     * @param array $data
     * @return PolicySchedule
     */
    public function create(array $data): PolicySchedule
    {
        return PolicySchedule::create($data);
    }

    /**
     * Update a policy schedule.
     *
     * @param PolicySchedule $item
     * @param array $data
     * @return PolicySchedule
     */
    public function update(PolicySchedule $item, array $data): PolicySchedule
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete a policy schedule.
     *
     * @param PolicySchedule $item
     * @return bool
     */
    public function delete(PolicySchedule $item): bool
    {
        return $item->delete();
    }
}
