<?php

namespace App\Services;

use App\Models\TimeEntry;

class TimeEntryService extends BaseService
{
    /**
     * List time entries with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return TimeEntry::with('user')->paginate(20);
    }

    /**
     * Find a time entry by id.
     *
     * @param int $id
     * @return TimeEntry|null
     */
    public function find(int $id): ?TimeEntry
    {
        return TimeEntry::find($id);
    }

    /**
     * Create a new time entry.
     *
     * @param array $data
     * @return TimeEntry
     */
    public function create(array $data): TimeEntry
    {
        return TimeEntry::create($data);
    }

    /**
     * Update a time entry.
     *
     * @param TimeEntry $entry
     * @param array $data
     * @return TimeEntry
     */
    public function update(TimeEntry $entry, array $data): TimeEntry
    {
        $entry->update($data);
        return $entry;
    }

    /**
     * Delete a time entry.
     *
     * @param TimeEntry $entry
     * @return bool
     */
    public function delete(TimeEntry $entry): bool
    {
        return $entry->delete();
    }
}
