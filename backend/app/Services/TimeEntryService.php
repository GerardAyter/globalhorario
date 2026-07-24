<?php

namespace App\Services;

use App\Models\TimeEntry;
use Illuminate\Pagination\LengthAwarePaginator;

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
        // Les taules de marcatges estan repartides per empresa: un usuari
        // sense empresa pròpia (founder/superadmin gestionant múltiples
        // empreses) no té una única taula a consultar aquí.
        if (! isset($filters['company_id'])) {
            return new LengthAwarePaginator([], 0, 20);
        }

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
