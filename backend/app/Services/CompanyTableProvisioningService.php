<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Crea, per a cada empresa, les seves pròpies taules de marcatges
 * (time_entries_{id}, time_entry_breaks_{id}, time_entry_logs_{id},
 * time_entry_edit_requests_{id}), clonant l'estructura de les taules
 * base (que es mantenen sempre buides, només com a plantilla d'esquema).
 *
 * Les claus foranes NO es confien a la còpia de CREATE TABLE ... LIKE
 * (el comportament varia entre versions de MySQL/MariaDB i, en tot cas,
 * apuntarien a les taules base en comptes de la taula germana de la
 * mateixa empresa). En comptes d'això, s'eliminen totes les FK que
 * pugui haver copiat i es recreen explícitament apuntant on toca.
 */
class CompanyTableProvisioningService
{
    private const SHARDED_TABLES = [
        'time_entries',
        'time_entry_breaks',
        'time_entry_logs',
        'time_entry_edit_requests',
    ];

    public function provisionForCompany(int $companyId): void
    {
        foreach (self::SHARDED_TABLES as $base) {
            $this->createShardTable($base, $companyId);
        }

        foreach (self::SHARDED_TABLES as $base) {
            $this->dropAllForeignKeys($this->shardTableName($base, $companyId));
        }

        $this->addForeignKeys($companyId);
    }

    public function tablesExistForCompany(int $companyId): bool
    {
        foreach (self::SHARDED_TABLES as $base) {
            if (! Schema::hasTable($this->shardTableName($base, $companyId))) {
                return false;
            }
        }
        return true;
    }

    private function shardTableName(string $base, int $companyId): string
    {
        return "{$base}_{$companyId}";
    }

    private function createShardTable(string $base, int $companyId): void
    {
        $target = $this->shardTableName($base, $companyId);
        if (Schema::hasTable($target)) {
            return;
        }
        DB::statement("CREATE TABLE `{$target}` LIKE `{$base}`");
    }

    private function dropAllForeignKeys(string $table): void
    {
        $dbName = DB::getDatabaseName();
        $fks = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$dbName, $table, 'FOREIGN KEY']
        );
        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $dbName = DB::getDatabaseName();
        $count = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$dbName, $table, $constraintName, 'FOREIGN KEY']
        );
        return $count && $count->c > 0;
    }

    private function addFk(string $table, string $column, string $refTable, string $refColumn, string $onDelete): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasTable($refTable)) {
            return;
        }
        if (! Schema::hasColumn($table, $column)) {
            return;
        }
        $constraintName = "{$table}_{$column}_fk";
        if ($this->foreignKeyExists($table, $constraintName)) {
            return;
        }
        DB::statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraintName}` " .
            "FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) ON DELETE {$onDelete}"
        );
    }

    private function addForeignKeys(int $companyId): void
    {
        $entries = $this->shardTableName('time_entries', $companyId);
        $breaks  = $this->shardTableName('time_entry_breaks', $companyId);
        $logs    = $this->shardTableName('time_entry_logs', $companyId);
        $edits   = $this->shardTableName('time_entry_edit_requests', $companyId);

        // time_entries -> taules compartides
        $this->addFk($entries, 'tenant_id', 'tenants', 'id', 'CASCADE');
        $this->addFk($entries, 'user_id', 'users', 'id', 'CASCADE');
        $this->addFk($entries, 'employee_id', 'employees', 'id', 'SET NULL');
        $this->addFk($entries, 'company_id', 'companies', 'id', 'SET NULL');
        $this->addFk($entries, 'shift_id', 'shifts', 'id', 'SET NULL');
        $this->addFk($entries, 'wa_event_id', 'whatsapp_events', 'id', 'SET NULL');

        // time_entry_breaks -> time_entries de la mateixa empresa
        $this->addFk($breaks, 'time_entry_id', $entries, 'id', 'CASCADE');

        // time_entry_logs -> time_entries de la mateixa empresa + taules compartides
        $this->addFk($logs, 'time_entry_id', $entries, 'id', 'CASCADE');
        $this->addFk($logs, 'user_id', 'users', 'id', 'RESTRICT');
        $this->addFk($logs, 'employee_id', 'employees', 'id', 'SET NULL');

        // time_entry_edit_requests -> time_entries/time_entry_breaks de la mateixa empresa + compartides
        $this->addFk($edits, 'time_entry_id', $entries, 'id', 'SET NULL');
        $this->addFk($edits, 'break_id', $breaks, 'id', 'SET NULL');
        $this->addFk($edits, 'employee_id', 'employees', 'id', 'CASCADE');
        $this->addFk($edits, 'requested_by_user_id', 'users', 'id', 'RESTRICT');
        $this->addFk($edits, 'reviewed_by_user_id', 'users', 'id', 'SET NULL');
    }
}
