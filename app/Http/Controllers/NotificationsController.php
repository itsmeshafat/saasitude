<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.notifications', [
            'notifications' => $request->user()->appNotifications()->latest('id')->paginate(20),
            'unreadCount' => $request->user()->appNotifications()->whereNull('read_at')->count(),
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
