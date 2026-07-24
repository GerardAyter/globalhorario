<?php

namespace App\Models\Traits;

use App\Services\CompanyContext;
use RuntimeException;

/**
 * Fa que el model apunti a una taula física diferent per cada empresa
 * (p.ex. "time_entries_7" en comptes de "time_entries"), ja que aquestes
 * taules es preveu que creixin molt i es volen mantenir separades per
 * empresa en comptes de compartir una única taula filtrada per company_id.
 *
 * Cada model que usi aquest trait ha de declarar el nom base de la taula:
 *   protected static string $shardBaseTable = 'time_entries';
 *
 * La resolució normal (dins d'una request HTTP autenticada) ve de
 * CompanyContext, que el middleware SetCompanyFromRequest omple a partir
 * de l'usuari autenticat. Per a usos fora del cicle de request (jobs de
 * cua, comandes artisan, backfills) cal indicar l'empresa explícitament
 * amb forCompany()/onCompany() o setShardCompanyId().
 */
trait HasCompanySharding
{
    protected ?int $shardCompanyId = null;

    public function getTable()
    {
        $companyId = $this->shardCompanyId ?? CompanyContext::getCompanyId();

        if (! $companyId) {
            throw new RuntimeException(
                'No hi ha cap empresa en context per resoldre la taula de ' . static::$shardBaseTable .
                '. Usa ' . static::class . '::forCompany($companyId) fora d\'una request HTTP autenticada.'
            );
        }

        return static::$shardBaseTable . '_' . $companyId;
    }

    public static function shardBaseTable(): string
    {
        return static::$shardBaseTable;
    }

    public function setShardCompanyId(?int $companyId): static
    {
        $this->shardCompanyId = $companyId;
        return $this;
    }

    public function getShardCompanyId(): ?int
    {
        return $this->shardCompanyId ?? CompanyContext::getCompanyId();
    }

    /** Nova instància (buida) apuntant explícitament a l'empresa indicada. */
    public static function forCompany(int $companyId): static
    {
        return (new static)->setShardCompanyId($companyId);
    }

    /** Query builder apuntant explícitament a l'empresa indicada. */
    public static function onCompany(int $companyId)
    {
        return static::forCompany($companyId)->newQuery();
    }

    public function newInstance($attributes = [], $exists = false)
    {
        $model = parent::newInstance($attributes, $exists);
        $model->setShardCompanyId($this->shardCompanyId);
        return $model;
    }
}
