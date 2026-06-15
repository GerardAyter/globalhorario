<?php

namespace App\Services;

use App\Models\Workplace;

class WorkplaceService extends BaseService
{
    /**
     * List workplaces.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return Workplace::query()->paginate(20);
    }

    /**
     * Find a workplace by id.
     *
     * @param int $id
     * @return Workplace|null
     */
    public function find(int $id): ?Workplace
    {
        return Workplace::find($id);
    }

    /**
     * Create a workplace.
     *
     * @param array $data
     * @return Workplace
     */
    public function create(array $data): Workplace
    {
        return Workplace::create($data);
    }

    /**
     * Update a workplace.
     *
     * @param Workplace $wp
     * @param array $data
     * @return Workplace
     */
    public function update(Workplace $wp, array $data): Workplace
    {
        $wp->update($data);
        return $wp;
    }

    /**
     * Delete a workplace.
     *
     * @param Workplace $wp
     * @return bool
     */
    public function delete(Workplace $wp): bool
    {
        return $wp->delete();
    }
}
