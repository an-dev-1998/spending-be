<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationSpending;
use App\Models\Spending;

class NotificationSpendingController extends Controller
{
    /**
     * Display a listing of the notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = NotificationSpending::with(['user', 'spending'])
            ->where('user_id', auth()->id());

        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $notifications
        ]);
    }

    /**
     * Mark a notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function markAsRead($id)
    {
        $notification = NotificationSpending::where('user_id', auth()->id())
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead()
    {
        NotificationSpending::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Remove the specified notification from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notification = NotificationSpending::where('user_id', auth()->id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json(null, 204);
    }

    /**
     * Get unread notifications count.
     *
     * @return \Illuminate\Http\Response
     */
    public function unreadCount()
    {
        $notifications = NotificationSpending::with(['user', 'spending'])
            ->where('user_id', auth()->id())
            ->where('is_read', false)
            ->get();

            return response()->json([
                'data' => $notifications
            ]);
    }
}
