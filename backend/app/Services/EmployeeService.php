<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeService extends BaseService
{
    /**
     * List employees with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return Employee::query()->paginate(20);
    }

    /**
     * Find an employee by id.
     *
     * @param int $id
     * @return Employee|null
     */
    public function find(int $id): ?Employee
    {
        return Employee::find($id);
    }

    /**
     * Create a new employee.
     *
     * @param array $data
     * @return Employee
     */
    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    /**
     * Update an existing employee.
     *
     * @param Employee $item
     * @param array $data
     * @return Employee
     */
    public function update(Employee $item, array $data): Employee
    {
        $item->update($data);
        return $item;
    }

    /**
     * Delete an employee.
     *
     * @param Employee $item
     * @return bool
     */
    public function delete(Employee $item): bool
    {
        return $item->delete();
    }

    /**
     * Get time entries for an employee.
     *
     * @param int $employeeId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function timeEntries(int $employeeId)
    {
        $employee = $this->find($employeeId);
        if (! $employee) {
            return null;
        }
        return $employee->timeEntries()->paginate(20);
    }
}
