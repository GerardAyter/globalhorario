<?php
$admin = \App\Models\User::withoutGlobalScopes()->find(3);
$tenant = \App\Models\Tenant::withoutGlobalScopes()->find($admin->tenant_id);
\App\Services\TenantContext::setTenant($tenant);
$svc = app(\App\Services\AbsenceRequestService::class);
$req = \App\Models\AbsenceRequest::find(8);
echo "Request: " . ($req ? "ID={$req->id} status={$req->status}" : "NULL") . "\n";
if ($req) {
    try {
        $r = $svc->approve($req, $admin, 'Aprovat per test');
        echo "OK: status={$r->status}\n";
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo $e->getFile() . ':' . $e->getLine() . "\n";
    }
}
