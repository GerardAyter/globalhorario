<?php

namespace App\Services;

use App\Models\Department;

class DepartmentService extends BaseService
{
    public function list(array $filters = [])
    {
        return Department::query()->paginate(20);
    }

    public function find(int $id): ?Department
    {
        return Department::find($id);
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update($data);
        return $department;
    }

    public function delete(Department $department): bool
    {
        return $department->delete();
    }
}
