<?php

namespace App\Services;

use App\Models\Shift;

class ShiftService extends BaseService
{
    public function list(array $filters = [])
    {
        $q = Shift::query()->orderBy('name');
        if (! empty($filters['company_id'])) {
            $q->where('company_id', $filters['company_id']);
        }
        return $q->get();
    }

    public function find(int $id): ?Shift
    {
        return Shift::find($id);
    }

    public function create(array $data): Shift
    {
        return Shift::create($data);
    }

    public function update(Shift $shift, array $data): Shift
    {
        $shift->update($data);
        return $shift;
    }

    public function delete(Shift $shift): bool
    {
        return $shift->delete();
    }
}
