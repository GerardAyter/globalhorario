<?php

namespace App\Services;

use App\Models\Company;

class CompanyService extends BaseService
{
    public function list(array $filters = [])
    {
        return Company::query()->paginate(20);
    }

    public function find(int $id): ?Company
    {
        return Company::find($id);
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);
        return $company;
    }

    public function delete(Company $company): bool
    {
        return $company->delete();
    }
}
