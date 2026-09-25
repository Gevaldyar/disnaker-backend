<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;

class AdminNotificationController extends Controller
{
    /**
     * Daftar notifikasi Admin.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi Admin berhasil diambil.',
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $notifications,
        ]);
    }

    /**
     * Jumlah notifikasi yang belum dibaca.
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Tandai satu notification sebagai dibaca.
     */
    public function markAsRead(
        Request $request,
        DatabaseNotification $notification
    ) {
        $user = $request->user();

        if (
            $notification->notifiable_type !== User::class ||
            (int) $notification->notifiable_id !== (int) $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi telah ditandai sebagai dibaca.',
        ]);
    }

    /**
     * Tandai semua notification sebagai dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $user->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah ditandai sebagai dibaca.',
        ]);
    }
}