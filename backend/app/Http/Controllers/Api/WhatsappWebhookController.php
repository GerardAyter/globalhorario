<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsappService;

class WhatsappWebhookController extends BaseController
{
    protected WhatsappService $service;

    public function __construct(WhatsappService $service)
    {
        $this->service = $service;
    }

    // GET verification
    public function verify(Request $request)
    {
        $verify_token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        if ($this->service->verifyToken($verify_token) && $challenge) {
            return response($challenge, 200);
        }
        return response('Forbidden', 403);
    }

    // POST events
    public function receive(Request $request)
    {
        $payload = $request->all();
        Log::debug('Whatsapp webhook received', ['payload' => $payload]);

        $created = $this->service->handlePayload($payload);

        return response('EVENT_RECEIVED', 200);
    }
}
