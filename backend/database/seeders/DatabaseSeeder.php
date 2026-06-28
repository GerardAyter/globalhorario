<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. TENANT ──────────────────────────────────────────────────────────
        $tenant = Tenant::create([
            'nom_intern'   => 'projectedigital',
            'pla'          => 'enterprise',
            'max_empleats' => 100,
            'actiu'        => true,
            'data_alta'    => now(),
        ]);

        TenantContext::setTenant($tenant);

        // ── 2. WHITELABEL CONFIG ────────────────────────────────────────────────
        DB::table('whitelabel_configs')->insert([
            'tenant_id'       => $tenant->id,
            'nom_producte'    => 'GlobalHorario',
            'domini_custom'   => null,
            'logo_url'        => null,
            'color_primari'   => '#1E40AF',
            'color_accent'    => '#3B82F6',
            'idioma_defecte'  => 'ca',
            'ocult_powered_by'=> false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // ── 3. COMPANY ─────────────────────────────────────────────────────────
        $companyId = DB::table('companies')->insertGetId([
            'tenant_id'           => $tenant->id,
            'name'                => 'Projecte Digital S.L.',
            'tax_id'              => 'B12345678',
            'timezone'            => 'Europe/Madrid',
            'country'             => 'ES',
            'collective_agreement'=> 'Conveni Col·lectiu TIC',
            'hr_configuration'    => json_encode([]),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // ── 4. POLICIES (cal crear-les abans de department i employee) ──────────
        $policyAbsenceId = DB::table('policy_absences')->insertGetId([
            'tenant_id'            => $tenant->id,
            'company_id'           => $companyId,
            'name'                 => 'Política Estàndard',
            'vacation_days_per_year'=> 23.00,
            'personal_days'        => 3,
            'max_consecutive_days' => 21,
            'min_notice_days'      => 7,
            'allow_accumulation'   => false,
            'max_accumulated_days' => null,
            'approval_required'    => true,
            'approval_levels'      => 1,
            'applies_to'           => 'all',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $policyScheduleId = DB::table('policy_schedules')->insertGetId([
            'tenant_id'                     => $tenant->id,
            'company_id'                    => $companyId,
            'name'                          => 'Horari Flexible',
            'type'                          => 'flexible',
            'tolerance_minutes'             => 15,
            'require_geolocation'           => false,
            'geolocation_radius_meters'     => null,
            'allow_remote_clocking'         => true,
            'max_hours_per_day'             => 10.00,
            'min_rest_between_shifts'       => 480,
            'require_approval_for_records'  => false,
            'auto_approve_if'               => null,
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ]);

        // ── 5. SHIFT ───────────────────────────────────────────────────────────
        $shiftId = DB::table('shifts')->insertGetId([
            'tenant_id'        => $tenant->id,
            'name'             => 'Jornada Normal',
            'company_id'       => $companyId,
            'color'            => '#10B981',
            'type'             => 'fixed',
            'start_time'       => '09:00:00',
            'end_time'         => '18:00:00',
            'crosses_midnight' => false,
            'days_of_week'     => json_encode([1, 2, 3, 4, 5]),
            'total_hours'      => 8.00,
            'min_rest_after'   => 480,
            'location_required'=> null,
            'active'           => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // ── 6. DEPARTMENT ──────────────────────────────────────────────────────
        $departmentId = DB::table('departments')->insertGetId([
            'tenant_id'  => $tenant->id,
            'name'       => 'Tecnologia',
            'company_id' => $companyId,
            'manager_id' => null,
            'location'   => 'Barcelona',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── 7. WORKPLACE ───────────────────────────────────────────────────────
        $workplaceId = DB::table('workplaces')->insertGetId([
            'tenant_id'             => $tenant->id,
            'title'                 => 'Desenvolupador',
            'department_id'         => $departmentId,
            'professional_category' => 'Grup A',
            'contribution_group'    => '1',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // ── 8. SUPERADMIN USER ─────────────────────────────────────────────────
        $userId = DB::table('users')->insertGetId([
            'tenant_id'         => $tenant->id,
            'name'              => 'Gerard',
            'email'             => 'gerard@projectedigital.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('12345678'),
            'role'              => 'founder',
            'remember_token'    => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Actualitzem el manager del departament ara que tenim user
        DB::table('departments')->where('id', $departmentId)->update(['manager_id' => $userId]);

        // ── 9. EMPLOYEE ────────────────────────────────────────────────────────
        DB::table('employees')->insert([
            'tenant_id'          => $tenant->id,
            'user_id'            => $userId,
            'company_id'         => $companyId,
            'department_id'      => $departmentId,
            'workplace_id'       => $workplaceId,
            'nom'                => 'Gerard',
            'cognoms'            => 'Ayter',
            'dni_nie'            => '12345678A',
            'nss'                => null,
            'data_naixement'     => '1990-01-01',
            'email'              => 'gerard@projectedigital.com',
            'telefon'            => null,
            'politica_absencia_id' => $policyAbsenceId,
            'politica_horari_id'   => $policyScheduleId,
            'torn_id'            => $shiftId,
            'percentatge_jornada'=> 100.00,
            'geoloc_requerida'   => false,
            'whatsapp_phone_hash'=> null,
            'whatsapp_verificat' => false,
            'actiu'              => true,
            'data_alta'          => now()->toDateString(),
            'data_baixa'         => null,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // ── 10. ABSENCE TYPES ──────────────────────────────────────────────────
        DB::table('absence_types')->insert([
            [
                'tenant_id'           => $tenant->id,
                'company_id'          => $companyId,
                'name'                => 'Vacances',
                'category'            => 'vacation',
                'requires_document'   => false,
                'paid'                => true,
                'max_days_per_year'   => 23,
                'counts_for_seniority'=> true,
                'legal_basis'         => 'ET art. 38',
                'calendar_color'      => '#3B82F6',
                'visible_to_company'  => true,
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'tenant_id'           => $tenant->id,
                'company_id'          => $companyId,
                'name'                => 'Baixa Mèdica',
                'category'            => 'sick',
                'requires_document'   => true,
                'paid'                => true,
                'max_days_per_year'   => null,
                'counts_for_seniority'=> false,
                'legal_basis'         => 'LGSS art. 169',
                'calendar_color'      => '#EF4444',
                'visible_to_company'  => true,
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'tenant_id'           => $tenant->id,
                'company_id'          => $companyId,
                'name'                => 'Assumptes Personals',
                'category'            => 'personal',
                'requires_document'   => false,
                'paid'                => true,
                'max_days_per_year'   => 3,
                'counts_for_seniority'=> false,
                'legal_basis'         => null,
                'calendar_color'      => '#F59E0B',
                'visible_to_company'  => true,
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);

        // ── 11. OVERTIME POLICY ────────────────────────────────────────────────
        DB::table('overtime_policies')->insert([
            'tenant_id'             => $tenant->id,
            'company_id'            => $companyId,
            'name'                  => 'Política Hores Extra Estàndard',
            'annual_limit'          => 80.00,
            'weekly_limit'          => null,
            'compensation'          => 'remuneration',
            'remuneration_multiplier'=> 1.75,
            'days_comp_per_hour'    => null,
            'comp_expiry_days'      => null,
            'approval_required'     => true,
            'notify_limit_percent'  => 80,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // ── 12. WORK CALENDAR ──────────────────────────────────────────────────
        DB::table('work_calendars')->insert([
            'tenant_id'        => $tenant->id,
            'company_id'       => $companyId,
            'year'             => 2026,
            'national_holidays'=> json_encode([
                '2026-01-01', '2026-01-06', '2026-04-03',
                '2026-05-01', '2026-08-15', '2026-10-12',
                '2026-11-01', '2026-12-06', '2026-12-08', '2026-12-25',
            ]),
            'local_holidays'   => json_encode(['2026-04-24', '2026-09-11']),
            'annual_hours'     => 1736.00,
            'geographic_zone'  => 'Catalunya',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}
