<?php

namespace App\Services;

use App\Models\Company;

class CompanyService extends BaseService
{
    public function __construct(private CompanyTableProvisioningService $tableProvisioning) {}

    public function list(array $filters = [])
    {
        $perPage = min((int)($filters['per_page'] ?? 20), 200);
        return Company::query()
            ->withCount('employees')
            ->with([
                'planFlags' => fn ($q) => $q->where('actiu', true)->select('id', 'tenant_id', 'company_id', 'feature'),
            ])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $id): ?Company
    {
        return Company::find($id);
    }

    public function findWithRelations(int $id): ?Company
    {
        return Company::with([
            'planFlags' => fn ($q) => $q->where('actiu', true)->select('id', 'tenant_id', 'company_id', 'feature'),
        ])
        ->withCount('employees')
        ->find($id);
    }

    public function create(array $data): Company
    {
        $company = Company::create($data);
        $this->tableProvisioning->provisionForCompany($company->id);
        return $company;
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
