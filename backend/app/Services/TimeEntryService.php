<?php

namespace App\Services;

use App\Models\TimeEntry;

class TimeEntryService extends BaseService
{
    public function list(array $filters = [])
    {
        return TimeEntry::with('user')->paginate(20);
    }

    public function find(int $id): ?TimeEntry
    {
        return TimeEntry::find($id);
    }

    public function create(array $data): TimeEntry
    {
        return TimeEntry::create($data);
    }

    public function update(TimeEntry $entry, array $data): TimeEntry
    {
        $entry->update($data);
        return $entry;
    }

    public function delete(TimeEntry $entry): bool
    {
        return $entry->delete();
    }
}
