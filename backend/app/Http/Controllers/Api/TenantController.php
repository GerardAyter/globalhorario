<?php

namespace App\Http\Controllers\Api;

use App\Models\PlanFeatureFlag;
use App\Models\User;
use App\Models\WhitelabelConfig;
use App\Services\TenantService;
use App\Http\Requests\TenantStoreRequest;
use App\Http\Requests\TenantUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TenantController extends BaseController
{
    protected TenantService $service;

    const MODULE_KEYS = ['time_tracking', 'documents', 'calendar'];

    public function __construct(TenantService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->list($request->all());
        return $this->success($data);
    }

    public function show($id)
    {
        $item = $this->service->findWithRelations((int)$id);
        if (! $item) {
            return $this->error('Tenant not found', null, 404);
        }
        return $this->success($item);
    }

    public function store(TenantStoreRequest $request)
    {
        $validated       = $request->validated();
        $logoBase64      = $validated['logo_base64']      ?? null;
        $faviconBase64   = $validated['favicon_base64']   ?? null;
        $modules         = $validated['modules']          ?? [];
        $superadminName  = $validated['superadmin_name']  ?? null;
        $superadminEmail = $validated['superadmin_email'] ?? null;
        $superadminPass  = $validated['superadmin_password'] ?? null;

        unset(
            $validated['logo_base64'], $validated['favicon_base64'],
            $validated['modules'],
            $validated['superadmin_name'], $validated['superadmin_email'], $validated['superadmin_password']
        );

        $tenant     = $this->service->create($validated);
        $logoUrl    = $logoBase64    ? $this->saveImage($tenant->id, $logoBase64,    'logos',    'logo')    : null;
        $faviconUrl = $faviconBase64 ? $this->saveImage($tenant->id, $faviconBase64, 'favicons', 'favicon') : null;

        $this->syncWhitelabel($tenant->id, $logoUrl, $faviconUrl);
        $this->syncModules($tenant->id, $modules);

        if ($superadminEmail && $superadminPass) {
            User::create([
                'tenant_id' => $tenant->id,
                'name'      => $superadminName ?: 'Superadministrador',
                'email'     => $superadminEmail,
                'password'  => Hash::make($superadminPass),
                'role'      => User::ROLE_SUPERADMIN,
            ]);
        }

        return $this->success(
            $this->service->findWithRelations($tenant->id),
            'Tenant created',
            null,
            201
        );
    }

    public function update(TenantUpdateRequest $request, $id)
    {
        $tenant = $this->service->find((int)$id);
        if (! $tenant) {
            return $this->error('Tenant not found', null, 404);
        }

        $validated       = $request->validated();
        $logoBase64      = $validated['logo_base64']      ?? null;
        $faviconBase64   = $validated['favicon_base64']   ?? null;
        $modules         = array_key_exists('modules', $validated) ? $validated['modules'] : null;
        $superadminName  = $validated['superadmin_name']     ?? null;
        $superadminEmail = $validated['superadmin_email']    ?? null;
        $superadminPass  = $validated['superadmin_password'] ?? null;

        unset(
            $validated['logo_base64'], $validated['favicon_base64'],
            $validated['modules'],
            $validated['superadmin_name'], $validated['superadmin_email'], $validated['superadmin_password']
        );

        $this->service->update($tenant, $validated);

        $logoUrl    = $logoBase64    ? $this->saveImage($tenant->id, $logoBase64,    'logos',    'logo')    : null;
        $faviconUrl = $faviconBase64 ? $this->saveImage($tenant->id, $faviconBase64, 'favicons', 'favicon') : null;

        if ($logoUrl !== null || $faviconUrl !== null) {
            $this->syncWhitelabel($tenant->id, $logoUrl, $faviconUrl);
        }

        if ($modules !== null) {
            $this->syncModules($tenant->id, $modules);
        }

        if ($superadminEmail && $superadminPass) {
            User::create([
                'tenant_id' => $tenant->id,
                'name'      => $superadminName ?: 'Superadministrador',
                'email'     => $superadminEmail,
                'password'  => Hash::make($superadminPass),
                'role'      => User::ROLE_SUPERADMIN,
            ]);
        }

        return $this->success(
            $this->service->findWithRelations($tenant->id),
            'Tenant updated'
        );
    }

    public function destroy($id)
    {
        $item = $this->service->find((int)$id);
        if (! $item) {
            return $this->error('Tenant not found', null, 404);
        }
        $this->service->delete($item);
        return $this->success(null, 'Tenant deleted', null, 204);
    }

    private function saveImage(int $tenantId, string $base64, string $folder, string $prefix): string
    {
        preg_match('/data:image\/(\w+);base64,/', $base64, $matches);
        $ext  = $matches[1] ?? 'png';
        $data = str_contains($base64, ',') ? explode(',', $base64, 2)[1] : $base64;

        $path = "{$folder}/tenant_{$tenantId}_{$prefix}.{$ext}";
        Storage::disk('public')->put($path, base64_decode($data));

        return Storage::disk('public')->url($path);
    }

    private function syncWhitelabel(int $tenantId, ?string $logoUrl, ?string $faviconUrl): void
    {
        $values = ['tenant_id' => $tenantId];
        if ($logoUrl    !== null) $values['logo_url']    = $logoUrl;
        if ($faviconUrl !== null) $values['favicon_url'] = $faviconUrl;

        WhitelabelConfig::updateOrCreate(['tenant_id' => $tenantId], $values);
    }

    private function syncModules(int $tenantId, array $modules): void
    {
        PlanFeatureFlag::where('tenant_id', $tenantId)
            ->whereIn('feature', self::MODULE_KEYS)
            ->delete();

        foreach ($modules as $feature) {
            PlanFeatureFlag::create([
                'tenant_id' => $tenantId,
                'feature'   => $feature,
                'actiu'     => true,
            ]);
        }
    }
}
