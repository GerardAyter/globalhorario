<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use App\Services\TenantContext;

class SetTenantFromRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow tenant management endpoints to run without a tenant (when middleware is global)
        $path = ltrim($request->path(), '/');
        if (str_starts_with($path, 'api/v1/tenants') || str_starts_with($path, 'api/v1/whitelabel-configs') || str_starts_with($path, 'webhook/whatsapp')) {
            return $next($request);
        }

        // 1) check header
        $header = $request->header('X-Tenant-ID');
        $tenant = null;

        if ($header) {
            if (is_numeric($header)) {
                $tenant = Tenant::find((int)$header);
            } else {
                $tenant = Tenant::where('nom_intern', $header)->first();
            }
        }

        // 2) if not header, try subdomain
        if (! $tenant) {
            $host = $request->getHost();
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $sub = $parts[0];
                $tenant = Tenant::where('nom_intern', $sub)->first();
            }
        }

        if (! $tenant || ! $tenant->actiu) {
            abort(404, 'Tenant not found or inactive');
        }

        TenantContext::setTenant($tenant);

        return $next($request);
    }
}
