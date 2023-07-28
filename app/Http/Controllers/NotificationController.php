<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Return a listing of the notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function notifications(Request $request)
    {
        $rowsPerPage = $request->query('rowsPerPage', 10);

        $notifications = $request->user()
            ->notifications()
            ->select(['id', 'data', 'read_at', 'created_at'])
            ->paginate($rowsPerPage);

        // foreach ($notifications as $item) {
        //     $item->is_fetched = true;
        //     $item->update();
        // }

        return response()->json($notifications);
    }

    /**
     * Return unread notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadNotificationsCount(Request $request)
    {
        $count = $request->user()
            ->unreadNotifications()
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }
    
    /**
     * Mark notification as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function touchNotification(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $id = $request->input('id');

        $notification = $request->user()
            ->unreadNotifications()
            ->find($id);
        
        if ($notification) {
            $notification->markAsRead();

            return response()->json([
                'success' => true,
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'There is no unread notifications.'
        ]);
    }

    /**
     * Return latest notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchNotifications(Request $request)
    {
        $limit = $request->query('limit', 5);

        $notifications = $request->user()
            ->notifications()
            // ->where('is_fetched', false)
            ->orderBy('created_at', 'DESC')
            ->select(['id', 'data', 'read_at', 'created_at'])
            ->take($limit)
            ->get();

        return response()->json($notifications);
    }
}
