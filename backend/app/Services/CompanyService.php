<?php

namespace App\Services;

use App\Models\Company;

class CompanyService extends BaseService
{
    /**
     * List companies with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $filters = [])
    {
        return Company::query()->paginate(20);
    }

    /**
     * Find a company by id.
     *
     * @param int $id
     * @return Company|null
     */
    public function find(int $id): ?Company
    {
        return Company::find($id);
    }

    /**
     * Create a new company.
     *
     * @param array $data
     * @return Company
     */
    public function create(array $data): Company
    {
        return Company::create($data);
    }

    /**
     * Update an existing company.
     *
     * @param Company $company
     * @param array $data
     * @return Company
     */
    public function update(Company $company, array $data): Company
    {
        $company->update($data);
        return $company;
    }

    /**
     * Delete a company.
     *
     * @param Company $company
     * @return bool
     */
    public function delete(Company $company): bool
    {
        return $company->delete();
    }
}
