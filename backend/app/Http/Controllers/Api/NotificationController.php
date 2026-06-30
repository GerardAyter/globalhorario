<?php

namespace App\Http\Controllers\Api;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function __construct(private NotificationService $service) {}

    public function my(Request $request)
    {
        $notifications = $this->service->forUser($request->user());
        $unread        = $this->service->unreadCount($request->user());
        return $this->success(['notifications' => $notifications, 'unread_count' => $unread]);
    }

    public function read(Request $request, int $id)
    {
        $this->service->markRead($id, $request->user());
        return $this->success(null, 'Notificació marcada com a llegida');
    }

    public function readAll(Request $request)
    {
        $this->service->markAllRead($request->user());
        return $this->success(null, 'Totes les notificacions marcades com a llegides');
    }
}
