<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappEvent;
use App\Models\TimeEntry;
use App\Models\VacationBalance;

class ProcessWhatsappMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $event;

    public function __construct(WhatsappEvent $event)
    {
        $this->event = $event;
        $this->onQueue('whatsapp');
    }

    public function handle(): void
    {
        $event = $this->event->fresh();
        $payload = $event->payload_raw;

        // Simple intent classification
        $text = null;
        if (is_array($payload) && isset($payload['text']['body'])) {
            $text = mb_strtolower($payload['text']['body']);
        } elseif (is_string($payload)) {
            $text = mb_strtolower($payload);
        } elseif (is_array($payload) && isset($payload['type']) && $payload['type'] === 'text') {
            $text = mb_strtolower(data_get($payload, 'text.body'));
        }

        $intent = 'desconegut';
        if ($text !== null) {
            if (str_contains($text, 'entrada') || str_contains($text, 'fichar') || str_contains($text, 'fitxar')) {
                $intent = 'fichar_entrada';
            } elseif (str_contains($text, 'sortida') || str_contains($text, 'salida') || str_contains($text, 'salir')) {
                $intent = 'fichar_sortida';
            } elseif (str_contains($text, 'saldo') || str_contains($text, 'vacances')) {
                $intent = 'consultar_saldo';
            } elseif (str_contains($text, 'vacan') || str_contains($text, 'vacanç')) {
                $intent = 'sollicitar_vacances';
            } elseif (str_contains($text, 'ajuda') || str_contains($text, 'help')) {
                $intent = 'ajuda';
            }
        }

        $event->intencio = $intent;
        $event->save();

        try {
            $session = $event->session;
            $employeeId = $session?->employee_id;

            if (in_array($intent, ['fichar_entrada', 'fichar_sortida'])) {
                if (! $employeeId) {
                    throw new \Exception('Employee not linked to session');
                }

                if ($intent === 'fichar_entrada') {
                    $te = TimeEntry::create([
                        'tenant_id' => $event->tenant_id,
                        'employee_id' => $employeeId,
                        'origin' => 'whatsapp',
                        'clock_in_at' => now(),
                        'integrity_hash' => sha1(now()->toDateTimeString()),
                        'wa_event_id' => $event->id,
                    ]);
                    $event->time_entry_id = $te->id;
                } else {
                    // find last open entry
                    $te = TimeEntry::where('tenant_id', $event->tenant_id)
                        ->where('employee_id', $employeeId)
                        ->whereNull('clock_out_at')
                        ->latest('clock_in_at')
                        ->first();
                    if ($te) {
                        $te->clock_out_at = now();
                        $te->wa_event_id = $event->id;
                        $te->save();
                        $event->time_entry_id = $te->id;
                    }
                }
            } elseif ($intent === 'consultar_saldo') {
                if (! $employeeId) {
                    throw new \Exception('Employee not linked to session');
                }
                $year = now()->year;
                $vb = VacationBalance::where('tenant_id', $event->tenant_id)
                    ->where('employee_id', $employeeId)
                    ->where('year', $year)
                    ->first();
                $balance = $vb ? ($vb->generated_days - $vb->taken_days) : null;
                $message = $balance !== null ? "El teu saldo d\'any $year és: $balance dies." : "No s'ha trobat saldo.";
                $this->sendWhatsappReply($event, $message);
            }

            $event->estat_proc = 'ok';
            $event->save();
        } catch (\Throwable $e) {
            Log::error('Error processing whatsapp event: '.$e->getMessage());
            $event->estat_proc = 'error';
            $event->error_msg = $e->getMessage();
            $event->save();
        }
    }

    protected function sendWhatsappReply(WhatsappEvent $event, string $text): void
    {
        // attempt to extract recipient number from payload
        $payload = $event->payload_raw;
        $from = data_get($payload, 'from') ?? data_get($payload, 'text.from') ?? data_get($payload, 'sender');

        $phoneNumberId = null;
        // try to derive phone_number_id from bot config via tenant
        $bot = \App\Models\WhatsappBotConfig::where('tenant_id', $event->tenant_id)->first();
        if ($bot) {
            $phoneNumberId = $bot->phone_number_id;
        }

        if (! $from || ! $phoneNumberId) {
            Log::warning('Cannot send whatsapp reply - missing recipient or phone_number_id');
            return;
        }

        $url = config('services.whatsapp.api_url')."/v18.0/{$phoneNumberId}/messages";

        try {
            Http::withToken(config('services.whatsapp.token'))
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $from,
                    'type' => 'text',
                    'text' => ['body' => $text],
                ]);
        } catch (\Throwable $e) {
            Log::error('Whatsapp send failed: '.$e->getMessage());
        }
    }
}
