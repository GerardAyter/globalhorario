<?php

namespace Database\Seeders;

use App\Models\AbsenceType;
use App\Models\Company;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultAbsenceTypesSeeder extends Seeder
{
    private const DEFAULTS = [
        [
            'name'               => 'Vacances',
            'category'           => 'vacation',
            'calendar_color'     => '#3B82F6',
            'paid'               => true,
            'requires_comment'   => false,
            'visible_to_company' => true,
        ],
        [
            'name'               => 'Dies personals',
            'category'           => 'personal',
            'calendar_color'     => '#8B5CF6',
            'paid'               => true,
            'requires_comment'   => false,
            'visible_to_company' => true,
        ],
        [
            'name'               => 'Baixa',
            'category'           => 'sick',
            'calendar_color'     => '#F59E0B',
            'paid'               => false,
            'requires_comment'   => false,
            'visible_to_company' => true,
        ],
        [
            'name'               => 'Altres',
            'category'           => 'other',
            'calendar_color'     => '#6B7280',
            'paid'               => false,
            'requires_comment'   => true,
            'visible_to_company' => true,
        ],
    ];

    public function run(): void
    {
        // Evitem el TenantScope global per poder consultar totes les empreses
        $companies = Company::all();

        foreach ($companies as $company) {
            // Comprovem sense TenantScope (context null = sense filtre)
            $existing = DB::table('absence_types')
                ->where('company_id', $company->id)
                ->count();

            if ($existing > 0) {
                $this->command?->info("Empresa #{$company->id} '{$company->name}' ja té {$existing} tipus — omès.");
                continue;
            }

            $now = now()->toDateTimeString();
            $rows = array_map(fn($t) => array_merge($t, [
                'company_id'       => $company->id,
                'tenant_id'        => $company->tenant_id,
                'requires_document'=> false,
                'max_days_per_year'=> null,
                'counts_for_seniority' => false,
                'legal_basis'      => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]), self::DEFAULTS);

            DB::table('absence_types')->insert($rows);

            $this->command?->info("Empresa #{$company->id} '{$company->name}': 4 tipus creats.");
        }
    }
}
