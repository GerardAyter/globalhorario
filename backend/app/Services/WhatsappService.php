<?php

namespace App\Services;

use App\Models\WhatsappBotConfig;
use App\Models\WhatsappSession;
use App\Models\WhatsappEvent;
use App\Jobs\ProcessWhatsappMessage;

class WhatsappService extends BaseService
{
    /**
     * Verify webhook token against stored bot configs.
     *
     * @param string|null $verifyToken
     * @return bool
     */
    public function verifyToken(?string $verifyToken): bool
    {
        if (! $verifyToken) {
            return false;
        }
        return WhatsappBotConfig::where('webhook_verify_token', $verifyToken)->exists();
    }

    /**
     * Handle incoming webhook payload: create sessions/events and dispatch processing jobs.
     *
     * @param array $payload
     * @return int Number of events created
     */
    public function handlePayload(array $payload): int
    {
        $created = 0;

        $from = data_get($payload, 'entry.0.changes.0.value.messages.0.from');
        $phoneHash = $from ? hash('sha256', $from) : null;

        $tenantId = null;
        $session = null;
        if ($phoneHash) {
            $session = WhatsappSession::where('wa_phone_hash', $phoneHash)->first();
            if ($session) {
                $tenantId = $session->tenant_id;
            }
        }

        $phoneNumberId = data_get($payload, 'entry.0.changes.0.value.metadata.phone_number_id');
        if (! $session && $phoneNumberId) {
            $bot = WhatsappBotConfig::where('phone_number_id', $phoneNumberId)->first();
            if ($bot) {
                $tenantId = $bot->tenant_id;
            }
        }

        if ($tenantId && ! $session && $phoneHash) {
            $session = WhatsappSession::create([
                'tenant_id' => $tenantId,
                'employee_id' => null,
                'wa_phone_hash' => $phoneHash,
                'estat' => 'pendent_verificacio',
            ]);
        }

        $messages = data_get($payload, 'entry.0.changes.0.value.messages', []);
        foreach ($messages as $msg) {
            $event = WhatsappEvent::create([
                'tenant_id' => $tenantId,
                'sessio_id' => $session?->id,
                'direccio' => 'entrada',
                'tipus_msg' => isset($msg['location']) ? 'location' : (isset($msg['type']) ? $msg['type'] : 'text'),
                'payload_raw' => $msg,
                'intencio' => null,
                'geoloc_lat' => isset($msg['location']) ? (float) data_get($msg, 'location.latitude') : null,
                'geoloc_lng' => isset($msg['location']) ? (float) data_get($msg, 'location.longitude') : null,
                'time_entry_id' => null,
                'estat_proc' => 'pendent',
                'error_msg' => null,
                'created_at' => now(),
            ]);

            ProcessWhatsappMessage::dispatch($event)->onQueue('whatsapp');
            $created++;
        }

        return $created;
    }
}
