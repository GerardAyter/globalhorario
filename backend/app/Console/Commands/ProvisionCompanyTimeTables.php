<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\CompanyTableProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProvisionCompanyTimeTables extends Command
{
    protected $signature = 'companies:provision-time-tables
                             {companyId? : Provisiona només aquesta empresa (per defecte, totes)}
                             {--migrate-data : Copia també les files existents de les taules compartides a les noves taules per empresa}';

    protected $description = "Crea les taules de marcatges pròpies de cada empresa (time_entries_{id} i taules relacionades)";

    // Taules filles amb FK obligatòria (NOT NULL) cap a time_entry_id:
    // n'hi ha prou amb filtrar per els ids de fitxatges de l'empresa.
    private const CHILD_TABLES_BY_ENTRY = [
        'time_entry_breaks' => 'time_entry_id',
        'time_entry_logs'   => 'time_entry_id',
    ];

    public function handle(CompanyTableProvisioningService $provisioning): int
    {
        $companyIdArg = $this->argument('companyId');
        $companies = $companyIdArg
            ? Company::where('id', $companyIdArg)->get()
            : Company::all();

        if ($companies->isEmpty()) {
            $this->error('No s\'ha trobat cap empresa.');
            return self::FAILURE;
        }

        foreach ($companies as $company) {
            $this->info("Empresa #{$company->id} ({$company->name})");
            $provisioning->provisionForCompany($company->id);
            $this->line('  Taules creades/verificades.');

            if ($this->option('migrate-data')) {
                $this->migrateExistingData((int) $company->id);
            }
        }

        $this->info('Fet.');
        return self::SUCCESS;
    }

    private function migrateExistingData(int $companyId): void
    {
        $entryIds = DB::table('time_entries')->where('company_id', $companyId)->pluck('id');

        if ($entryIds->isEmpty()) {
            $this->line('  Cap fitxatge a migrar.');
            return;
        }

        $entriesTable = "time_entries_{$companyId}";
        $this->line("  Migrant {$entryIds->count()} fitxatges a {$entriesTable}...");
        $this->copyRows('time_entries', $entriesTable, fn ($q) => $q->where('company_id', $companyId));

        foreach (self::CHILD_TABLES_BY_ENTRY as $base => $fk) {
            $target = "{$base}_{$companyId}";
            $count = DB::table($base)->whereIn($fk, $entryIds)->count();
            if ($count === 0) {
                continue;
            }
            $this->line("  Migrant {$count} files de {$base} a {$target}...");
            $this->copyRows($base, $target, fn ($q) => $q->whereIn($fk, $entryIds));
        }

        // time_entry_edit_requests.time_entry_id és NULLABLE (les sol·licituds
        // d'eliminació aprovades sobreviuen amb time_entry_id = NULL un cop
        // eliminat el fitxatge original), així que cal identificar-les per
        // l'empresa de l'empleat en comptes de només per time_entry_id.
        $editsTarget = "time_entry_edit_requests_{$companyId}";
        $editsCount = DB::table('time_entry_edit_requests as ter')
            ->join('employees as e', 'e.id', '=', 'ter.employee_id')
            ->where('e.company_id', $companyId)
            ->count();
        if ($editsCount > 0) {
            $this->line("  Migrant {$editsCount} files de time_entry_edit_requests a {$editsTarget}...");
            DB::table('time_entry_edit_requests as ter')
                ->join('employees as e', 'e.id', '=', 'ter.employee_id')
                ->where('e.company_id', $companyId)
                ->select('ter.*')
                ->orderBy('ter.id')
                ->chunkById(500, function ($rows) use ($editsTarget) {
                    $batch = [];
                    foreach ($rows as $row) {
                        $batch[] = (array) $row;
                    }
                    if ($batch) {
                        DB::table($editsTarget)->insert($batch);
                    }
                }, 'ter.id', 'id');
        }
    }

    private function copyRows(string $sourceTable, string $targetTable, \Closure $constraint): void
    {
        $query = DB::table($sourceTable);
        $constraint($query);

        $query->orderBy('id')->chunkById(500, function ($rows) use ($targetTable) {
            $batch = [];
            foreach ($rows as $row) {
                $batch[] = (array) $row;
            }
            if ($batch) {
                DB::table($targetTable)->insert($batch);
            }
        });
    }
}
