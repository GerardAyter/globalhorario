<?php

namespace App\Http\Controllers\Api;

use App\Services\CompanyService;
use App\Http\Requests\CompanyStoreRequest;
use App\Http\Requests\CompanyUpdateRequest;
use Illuminate\Http\Request;

class CompanyController extends BaseController
{
    protected CompanyService $service;

    public function __construct(CompanyService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $companies = $this->service->list($request->all());
        return $this->success($companies, 'Companies retrieved');
    }

    public function show($id)
    {
        $company = $this->service->find((int)$id);
        if (! $company) {
            return $this->error('Company not found', null, 404);
        }
        return $this->success($company);
    }

    public function store(CompanyStoreRequest $request)
    {
        $company = $this->service->create($request->validated());
        return $this->success($company, 'Company created', null, 201);
    }

    public function update(CompanyUpdateRequest $request, $id)
    {
        $company = $this->service->find((int)$id);
        if (! $company) {
            return $this->error('Company not found', null, 404);
        }
        $company = $this->service->update($company, $request->validated());
        return $this->success($company, 'Company updated');
    }

    public function destroy($id)
    {
        $company = $this->service->find((int)$id);
        if (! $company) {
            return $this->error('Company not found', null, 404);
        }
        $this->service->delete($company);
        return $this->success(null, 'Company deleted', null, 204);
    }
}
