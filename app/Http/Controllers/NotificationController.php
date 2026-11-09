<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarkNotificationRequest;
use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', ['notifications' => AppNotification::where('user_id', $request->user()->id)->latest()->paginate(20)]);
    }

    public function update(MarkNotificationRequest $request, AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->update(['read_at' => $request->boolean('read') ? now() : null]);

        return back()->with('success', 'Status notifikasi diperbarui.');
    }
}
