<?php

namespace App\Services;

use App\Models\PlatformSetting;

/**
 * Configuració global de la plataforma: una única fila (singleton),
 * a diferència del whitelabel per-tenant.
 */
class PlatformSettingService extends BaseService
{
    public function get(): PlatformSetting
    {
        return PlatformSetting::firstOrCreate(['id' => 1], ['nom_producte' => 'GlobalHorario']);
    }

    public function update(array $data): PlatformSetting
    {
        $settings = $this->get();
        $settings->update($data);
        return $settings;
    }
}
