<?php

namespace App\Services;

use App\Models\WorkCalendar;

class WorkCalendarService extends BaseService
{
    /**
     * List work calendars.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return WorkCalendar::query()->paginate(20);
    }

    /**
     * Find a work calendar by id.
     *
     * @param int $id
     * @return WorkCalendar|null
     */
    public function find(int $id): ?WorkCalendar
    {
        return WorkCalendar::find($id);
    }

    /**
     * Create a work calendar.
     *
     * @param array $data
     * @return WorkCalendar
     */
    public function create(array $data): WorkCalendar
    {
        return WorkCalendar::create($data);
    }

    /**
     * Update a work calendar.
     *
     * @param WorkCalendar $item
     * @param array $data
     * @return WorkCalendar
     */
    public function update(WorkCalendar $item, array $data): WorkCalendar
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete a work calendar.
     *
     * @param WorkCalendar $item
     * @return bool
     */
    public function delete(WorkCalendar $item): bool
    {
        return $item->delete();
    }
}
