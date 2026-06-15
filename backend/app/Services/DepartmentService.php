<?php

namespace App\Services;

use App\Models\Department;

class DepartmentService extends BaseService
{
    /**
     * List departments with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return Department::query()->paginate(20);
    }

    /**
     * Find a department by id.
     *
     * @param int $id
     * @return Department|null
     */
    public function find(int $id): ?Department
    {
        return Department::find($id);
    }

    /**
     * Create a new department.
     *
     * @param array $data
     * @return Department
     */
    public function create(array $data): Department
    {
        return Department::create($data);
    }

    /**
     * Update a department.
     *
     * @param Department $department
     * @param array $data
     * @return Department
     */
    public function update(Department $department, array $data): Department
    {
        $department->update($data);
        return $department;
    }

    /**
     * Delete a department.
     *
     * @param Department $department
     * @return bool
     */
    public function delete(Department $department): bool
    {
        return $department->delete();
    }
}
