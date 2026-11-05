<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    /** JSON poll for the topbar bell: unread count + latest 5 notifications. */
    public function poll(Request $request): JsonResponse
    {
        $notifications = $request->user()->appNotifications()->latest('id')->limit(5)->get();

        return response()->json([
            'unread' => $request->user()->appNotifications()->whereNull('read_at')->count(),
            'items' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'action_url' => $n->action_url,
                'read' => $n->read_at !== null,
                'ago' => $n->created_at?->diffForHumans(),
            ]),
        ]);
    }

    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $query = $request->user()->appNotifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return view('dashboard.notifications', [
            'notifications' => $query->latest('id')->paginate(20)->withQueryString(),
            'unreadCount' => $request->user()->appNotifications()->whereNull('read_at')->count(),
            'totalCount' => $request->user()->appNotifications()->count(),
            'filter' => $filter,
        ]);
    }

    public function markRead(Request $request, int $id): RedirectResponse
    {
        $request->user()->appNotifications()->whereKey($id)->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->route('dashboard.notifications');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->route('dashboard.notifications')->with('success', 'All notifications marked as read.');
    }

    public function updatePreferences(Request $request, SettingsService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'types' => ['required', 'array'],
            'types.*' => ['string', 'max:50'],
        ]);

        $userId = $request->user()->id;

        foreach ($validated['types'] as $type) {
            foreach (['email', 'push'] as $channel) {
                $settings->set(
                    "user.{$userId}.notify.{$type}.{$channel}",
                    $request->boolean("pref_{$type}_{$channel}") ? '1' : '0',
                );
            }
        }

        return redirect()->route('dashboard.notifications')->with('success', 'Notification preferences saved.');
    }
}
