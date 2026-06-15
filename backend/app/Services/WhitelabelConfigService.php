<?php

namespace App\Services;

use App\Models\WhitelabelConfig;

class WhitelabelConfigService extends BaseService
{
    public function list(array $filters = [])
    {
        return WhitelabelConfig::query()->paginate(20);
    }

    public function find(int $id): ?WhitelabelConfig
    {
        return WhitelabelConfig::find($id);
    }

    public function create(array $data): WhitelabelConfig
    {
        return WhitelabelConfig::create($data);
    }

    public function update(WhitelabelConfig $item, array $data): WhitelabelConfig
    {
        $item->update($data);
        return $item;
    }

    public function delete(WhitelabelConfig $item): bool
    {
        return $item->delete();
    }
}
