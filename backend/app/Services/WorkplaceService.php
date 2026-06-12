<?php

namespace App\Services;

use App\Models\Workplace;

class WorkplaceService extends BaseService
{
    public function list(array $filters = [])
    {
        return Workplace::query()->paginate(20);
    }

    public function find(int $id): ?Workplace
    {
        return Workplace::find($id);
    }

    public function create(array $data): Workplace
    {
        return Workplace::create($data);
    }

    public function update(Workplace $wp, array $data): Workplace
    {
        $wp->update($data);
        return $wp;
    }

    public function delete(Workplace $wp): bool
    {
        return $wp->delete();
    }
}
