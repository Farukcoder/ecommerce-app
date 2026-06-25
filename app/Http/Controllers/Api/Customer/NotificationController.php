<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customer = $this->authorizeCustomer($request);

        $notifications = $customer->notifications()
            ->latest()
            ->paginate((int) $request->integer('per_page', 15));

        $data = $notifications->getCollection()->map(fn ($notification) => [
            'id' => $notification->id,
            'type' => $notification->data['type'] ?? 'general',
            'data' => $notification->data,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ]);

        return response()->json([
            'data' => $data,
            'unread_count' => $customer->unreadNotifications()->count(),
            'pagination' => [
                'total' => $notifications->total(),
                'page' => $notifications->currentPage(),
                'limit' => $notifications->perPage(),
                'total_pages' => $notifications->lastPage(),
            ],
        ]);
    }

    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $customer = $this->authorizeCustomer($request);

        $notification = $customer->notifications()->where('id', $notificationId)->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'unread_count' => $customer->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $customer = $this->authorizeCustomer($request);

        $customer->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'unread_count' => 0,
        ]);
    }

    private function authorizeCustomer(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            throw ValidationException::withMessages([
                'user' => 'Unauthorized.',
            ]);
        }

        return $user;
    }
}
