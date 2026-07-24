<?php

namespace App\Http\Controllers\Api;

use App\Services\PlatformSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatformSettingController extends BaseController
{
    public function __construct(private PlatformSettingService $service) {}

    public function show()
    {
        return $this->success($this->service->get());
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nom_producte'   => 'required|string|max:255',
            'color_primari'  => 'nullable|string|max:20',
            'email_suport'   => 'nullable|email|max:255',
            'peu_legal'      => 'nullable|string|max:2000',
            'logo_base64'    => 'nullable|string',
            'favicon_base64' => 'nullable|string',
        ]);

        $logoBase64    = $validated['logo_base64']    ?? null;
        $faviconBase64 = $validated['favicon_base64'] ?? null;
        unset($validated['logo_base64'], $validated['favicon_base64']);

        $settings = $this->service->update($validated);

        $updates = array_filter([
            'logo_url'    => $logoBase64    ? $this->saveImage($logoBase64,    'platform', 'logo')    : null,
            'favicon_url' => $faviconBase64 ? $this->saveImage($faviconBase64, 'platform', 'favicon') : null,
        ]);
        if ($updates) $settings->update($updates);

        return $this->success($settings->fresh(), 'Configuració de la plataforma actualitzada');
    }

    private function saveImage(string $base64, string $folder, string $prefix): string
    {
        preg_match('/data:image\/(\w+);base64,/', $base64, $matches);
        $ext  = $matches[1] ?? 'png';
        $data = str_contains($base64, ',') ? explode(',', $base64, 2)[1] : $base64;
        $path = "{$folder}/{$prefix}.{$ext}";

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->put($path, base64_decode($data));

        return $disk->url($path);
    }
}
