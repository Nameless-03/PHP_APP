<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificacionController extends Controller
{
    /**
     * Get unread notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $notificaciones = $request->user()->unreadNotifications()->latest()->get();
        return response()->json(['data' => $notificaciones]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function marcarLeida(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notificación marcada como leída']);
        }
        
        return response()->json(['message' => 'Notificación no encontrada'], 404);
     }

    /**
     * Mark all notifications as read.
     */
    public function marcarTodasLeidas(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas']);
    }
}
