<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeService extends BaseService
{
    public function list(array $filters = [])
    {
        $q = Employee::with(['user', 'company', 'department']);
        if (!empty($filters['company_id'])) {
            $q->where('company_id', $filters['company_id']);
        }
        return $q->paginate(20);
    }

    public function find(int $id): ?Employee
    {
        return Employee::with(['user', 'company', 'department'])->find($id);
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(Employee $item, array $data): Employee
    {
        $item->update($data);
        return $item->load(['user', 'company']);
    }

    public function delete(Employee $item): bool
    {
        return $item->delete();
    }

    public function timeEntries(int $employeeId)
    {
        $employee = $this->find($employeeId);
        if (! $employee) {
            return null;
        }
        return $employee->timeEntries()->paginate(20);
    }
}
