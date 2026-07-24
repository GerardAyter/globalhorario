<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Services\CompanyContext;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Resolveix l'empresa de l'usuari autenticat i la deixa disponible via
 * CompanyContext perquè els models amb HasCompanySharding sàpiguen a
 * quina taula per-empresa (time_entries_{id}, etc.) han d'apuntar.
 *
 * S'executa al grup de middleware 'api', abans que 'auth:sanctum' (que
 * només s'aplica a nivell de ruta), per això resol el token manualment
 * igual que SetTenantFromRequest.
 */
class SetCompanyFromRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = ltrim($request->path(), '/');

        // Endpoints no lligats a una sola empresa (multi-empresa o públics)
        if (
            str_starts_with($path, 'api/auth/') ||
            str_starts_with($path, 'api/v1/tenants') ||
            str_starts_with($path, 'api/v1/whitelabel-configs') ||
            str_starts_with($path, 'api/v1/companies') ||
            str_starts_with($path, 'webhook/whatsapp')
        ) {
            return $next($request);
        }

        if ($bearerToken = $request->bearerToken()) {
            $pat = PersonalAccessToken::findToken($bearerToken);
            if ($pat && $pat->tokenable instanceof User) {
                $companyId = $pat->tokenable->company_id ?? $pat->tokenable->employee?->company_id;
                if ($companyId) {
                    CompanyContext::setCompanyId($companyId);
                }
            }
        }

        return $next($request);
    }
}
