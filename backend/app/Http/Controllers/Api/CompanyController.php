<?php

namespace App\Http\Controllers\Api;

use App\Models\CompanyPlanFlag;
use App\Models\Employee;
use App\Models\User;
use App\Services\CompanyService;
use App\Http\Requests\CompanyStoreRequest;
use App\Http\Requests\CompanyUpdateRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CompanyController extends BaseController
{
    protected CompanyService $service;

    const MODULE_KEYS = ['time_tracking', 'documents', 'calendar'];

    public function __construct(CompanyService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $companies = $this->service->list($request->all());
        return $this->success($companies);
    }

    public function show(int $id)
    {
        $company = $this->service->findWithRelations($id);
        if (! $company) {
            return $this->error('Company not found', null, 404);
        }
        return $this->success($company);
    }

    public function store(CompanyStoreRequest $request)
    {
        $validated     = $request->validated();
        $logoBase64    = $validated['logo_base64']    ?? null;
        $faviconBase64 = $validated['favicon_base64'] ?? null;
        $modules       = $validated['modules']        ?? [];
        $adminName     = $validated['admin_name']     ?? null;
        $adminEmail    = $validated['admin_email']    ?? null;
        $adminPass     = $validated['admin_password'] ?? null;

        unset(
            $validated['logo_base64'], $validated['favicon_base64'],
            $validated['modules'],
            $validated['admin_name'], $validated['admin_email'], $validated['admin_password']
        );

        $company    = $this->service->create($validated);
        $logoUrl    = $logoBase64    ? $this->saveImage($company->id, $logoBase64,    'company-logos',    'logo')    : null;
        $faviconUrl = $faviconBase64 ? $this->saveImage($company->id, $faviconBase64, 'company-favicons', 'favicon') : null;

        $updates = array_filter(['logo_url' => $logoUrl, 'favicon_url' => $faviconUrl]);
        if ($updates) $company->update($updates);

        $this->syncModules($company->id, $modules);

        if ($adminEmail && $adminPass) {
            $fullName = trim($adminName ?: 'Administrador');
            $parts    = explode(' ', $fullName, 2);
            $user     = User::create([
                'tenant_id'  => $company->tenant_id,
                'company_id' => $company->id,
                'name'       => $fullName,
                'email'      => $adminEmail,
                'password'   => Hash::make($adminPass),
                'role'       => User::ROLE_ADMIN,
            ]);
            Employee::create([
                'company_id' => $company->id,
                'user_id'    => $user->id,
                'nom'        => $parts[0],
                'cognoms'    => $parts[1] ?? '',
                'email'      => $adminEmail,
                'data_alta'  => now()->toDateString(),
                'actiu'      => true,
            ]);
        }

        return $this->success(
            $this->service->findWithRelations($company->id),
            'Company created',
            null,
            201
        );
    }

    public function update(CompanyUpdateRequest $request, int $id)
    {
        $company = $this->service->find($id);
        if (! $company) {
            return $this->error('Company not found', null, 404);
        }

        $validated     = $request->validated();
        $logoBase64    = $validated['logo_base64']    ?? null;
        $faviconBase64 = $validated['favicon_base64'] ?? null;
        $modules       = array_key_exists('modules', $validated) ? $validated['modules'] : null;
        $adminName     = $validated['admin_name']     ?? null;
        $adminEmail    = $validated['admin_email']    ?? null;
        $adminPass     = $validated['admin_password'] ?? null;

        unset(
            $validated['logo_base64'], $validated['favicon_base64'],
            $validated['modules'],
            $validated['admin_name'], $validated['admin_email'], $validated['admin_password']
        );

        $this->service->update($company, $validated);

        $logoUrl    = $logoBase64    ? $this->saveImage($company->id, $logoBase64,    'company-logos',    'logo')    : null;
        $faviconUrl = $faviconBase64 ? $this->saveImage($company->id, $faviconBase64, 'company-favicons', 'favicon') : null;

        $updates = array_filter(['logo_url' => $logoUrl, 'favicon_url' => $faviconUrl]);
        if ($updates) $company->update($updates);

        if ($modules !== null) {
            $this->syncModules($company->id, $modules);
        }

        if ($adminEmail && $adminPass) {
            $fullName = trim($adminName ?: 'Administrador');
            $parts    = explode(' ', $fullName, 2);
            $user     = User::create([
                'tenant_id'  => $company->tenant_id,
                'company_id' => $company->id,
                'name'       => $fullName,
                'email'      => $adminEmail,
                'password'   => Hash::make($adminPass),
                'role'       => User::ROLE_ADMIN,
            ]);
            Employee::create([
                'company_id' => $company->id,
                'user_id'    => $user->id,
                'nom'        => $parts[0],
                'cognoms'    => $parts[1] ?? '',
                'email'      => $adminEmail,
                'data_alta'  => now()->toDateString(),
                'actiu'      => true,
            ]);
        }

        return $this->success(
            $this->service->findWithRelations($company->id),
            'Company updated'
        );
    }

    /** Perfil de l'empresa pròpia (Admin+) */
    public function my(Request $request)
    {
        $company = $this->resolveOwnCompany($request);
        if (! $company) {
            return $this->error("No s'ha trobat l'empresa", null, 404);
        }
        return $this->success($company);
    }

    /** Actualitzar l'empresa pròpia (Admin+, camps de perfil únicament) */
    public function updateMy(Request $request)
    {
        $company = $this->resolveOwnCompany($request);
        if (! $company) {
            return $this->error("No s'ha trobat l'empresa", null, 404);
        }

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'nom_legal'            => ['nullable', 'string', 'max:255', Rule::unique('companies', 'nom_legal')->ignore($company->id)],
            'tax_id'               => ['nullable', 'string', 'max:100', Rule::unique('companies', 'tax_id')->ignore($company->id)],
            'adreca_facturacio'    => 'nullable|string|max:500',
            'telefon'              => 'nullable|string|max:30',
            'email_contacte'       => 'nullable|email|max:255',
            'persona_contacte'     => 'nullable|string|max:255',
            'timezone'             => 'nullable|string|max:100',
            'country'              => 'nullable|string|max:100',
            'collective_agreement' => 'nullable|string|max:255',
            'logo_base64'          => 'nullable|string',
            'favicon_base64'       => 'nullable|string',
        ]);

        $logoBase64    = $validated['logo_base64']    ?? null;
        $faviconBase64 = $validated['favicon_base64'] ?? null;
        unset($validated['logo_base64'], $validated['favicon_base64']);

        $this->service->update($company, $validated);

        $logoUrl    = $logoBase64    ? $this->saveImage($company->id, $logoBase64,    'company-logos',    'logo')    : null;
        $faviconUrl = $faviconBase64 ? $this->saveImage($company->id, $faviconBase64, 'company-favicons', 'favicon') : null;

        $updates = array_filter(['logo_url' => $logoUrl, 'favicon_url' => $faviconUrl]);
        if ($updates) $company->update($updates);

        return $this->success($this->service->find($company->id), 'Empresa actualitzada');
    }

    private function resolveOwnCompany(Request $request): ?Company
    {
        /** @var User $user */
        $user      = $request->user();
        $companyId = $user->company_id ?? $user->employee?->company_id;
        if (! $companyId) return null;
        return $this->service->find($companyId);
    }

    public function destroy(int $id)
    {
        $company = $this->service->find($id);
        if (! $company) {
            return $this->error('Company not found', null, 404);
        }
        $this->service->delete($company);
        return $this->success(null, 'Company deleted', null, 204);
    }

    private function saveImage(int $companyId, string $base64, string $folder, string $prefix): string
    {
        preg_match('/data:image\/(\w+);base64,/', $base64, $matches);
        $ext  = $matches[1] ?? 'png';
        $data = str_contains($base64, ',') ? explode(',', $base64, 2)[1] : $base64;
        $path = "{$folder}/company_{$companyId}_{$prefix}.{$ext}";
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->put($path, base64_decode($data));
        return $disk->url($path);
    }

    private function syncModules(int $companyId, array $modules): void
    {
        CompanyPlanFlag::where('company_id', $companyId)
            ->whereIn('feature', self::MODULE_KEYS)
            ->delete();

        foreach ($modules as $feature) {
            CompanyPlanFlag::create([
                'company_id' => $companyId,
                'feature'    => $feature,
                'actiu'      => true,
            ]);
        }
    }
}
