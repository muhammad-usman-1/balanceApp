<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification as NotificationModel;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = NotificationModel::withTrashed()->orderBy('created_at', 'desc')->get();
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        NotificationModel::create($request->only(['title', 'message']));

        return redirect()->route('admin.notifications.index')->with('success', 'Notification created successfully');
    }

    public function edit(NotificationModel $notification)
    {
        return view('admin.notifications.edit', compact('notification'));
    }

    public function update(Request $request, NotificationModel $notification)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $notification->update($request->only(['title', 'message']));

        return redirect()->route('admin.notifications.index')->with('success', 'Notification updated successfully');
    }

    public function destroy(NotificationModel $notification)
    {
        $notification->delete();
        return redirect()->route('admin.notifications.index')->with('success', 'Notification deleted successfully');
    }
}

