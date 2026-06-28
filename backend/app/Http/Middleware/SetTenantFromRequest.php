<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Laravel\Sanctum\PersonalAccessToken;

class SetTenantFromRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = ltrim($request->path(), '/');

        // Endpoints that always bypass tenant resolution
        if (
            str_starts_with($path, 'api/auth/') ||
            str_starts_with($path, 'api/v1/tenants') ||
            str_starts_with($path, 'api/v1/whitelabel-configs') ||
            str_starts_with($path, 'webhook/whatsapp')
        ) {
            return $next($request);
        }

        // Founders bypass tenant requirement (they manage all tenants)
        if ($bearerToken = $request->bearerToken()) {
            $pat = PersonalAccessToken::findToken($bearerToken);
            if ($pat && $pat->tokenable instanceof User && $pat->tokenable->role === User::ROLE_FOUNDER) {
                // Optionally set tenant context if header is provided
                if ($tenantHeader = $request->header('X-Tenant-ID')) {
                    $tenant = is_numeric($tenantHeader)
                        ? Tenant::find((int) $tenantHeader)
                        : Tenant::where('nom_intern', $tenantHeader)->first();
                    if ($tenant && $tenant->actiu) {
                        TenantContext::setTenant($tenant);
                    }
                }
                return $next($request);
            }
        }

        // Resolve tenant from header or subdomain
        $tenant = null;
        $header = $request->header('X-Tenant-ID');

        if ($header) {
            $tenant = is_numeric($header)
                ? Tenant::find((int) $header)
                : Tenant::where('nom_intern', $header)->first();
        }

        if (! $tenant) {
            $parts = explode('.', $request->getHost());
            if (count($parts) > 2) {
                $tenant = Tenant::where('nom_intern', $parts[0])->first();
            }
        }

        // Fallback: resolve from the authenticated user's own tenant_id
        if (! $tenant && $bearerToken) {
            $pat = PersonalAccessToken::findToken($bearerToken);
            if ($pat && $pat->tokenable instanceof User && $pat->tokenable->tenant_id) {
                $tenant = Tenant::find($pat->tokenable->tenant_id);
            }
        }

        if (! $tenant || ! $tenant->actiu) {
            abort(404, 'Tenant not found or inactive');
        }

        TenantContext::setTenant($tenant);

        return $next($request);
    }
}
