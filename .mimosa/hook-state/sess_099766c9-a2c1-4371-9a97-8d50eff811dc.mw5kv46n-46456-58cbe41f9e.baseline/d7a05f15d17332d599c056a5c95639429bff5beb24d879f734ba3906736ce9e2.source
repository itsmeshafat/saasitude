<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->appNotifications()
            ->orderByDesc('id')
            ->cursorPaginate(20);

        return response()->json([
            'data' => collect($notifications->items())->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'action_url' => $n->action_url,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
            ]),
            'meta' => ['next_cursor' => $notifications->nextCursor()?->encode()],
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = $request->user()->appNotifications()->whereKey($id)->first();

        if ($notification === null) {
            return response()->json([
                'message' => 'Notification not found.',
                'code' => 'not_found',
                'errors' => [],
            ], 404);
        }

        $notification->markRead();

        return response()->json(['message' => 'Marked as read.', 'code' => 'notification_read', 'errors' => []]);
    }

    /** Per-type email/push preferences, stored in user settings. */
    public function preferences(Request $request, SettingsService $settings): JsonResponse
    {
        return response()->json(['data' => $this->allPreferences($request->user()->id, $settings)]);
    }

    public function updatePreferences(Request $request, SettingsService $settings): JsonResponse
    {
        $validated = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.type' => ['required', 'string', 'max:50'],
            'preferences.*.email' => ['nullable', 'boolean'],
            'preferences.*.push' => ['nullable', 'boolean'],
        ]);

        $userId = $request->user()->id;

        foreach ($validated['preferences'] as $pref) {
            $type = $pref['type'];

            foreach (['email', 'push'] as $channel) {
                if (array_key_exists($channel, $pref)) {
                    $settings->set("user.{$userId}.notify.{$type}.{$channel}", (bool) $pref[$channel] ? '1' : '0');
                }
            }
        }

        return response()->json(['data' => $this->allPreferences($userId, $settings)]);
    }

    private function allPreferences(int $userId, SettingsService $settings): array
    {
        $types = ['trial.started', 'trial.ending', 'payment.succeeded', 'payment.failed', 'plan.changed', 'support.reply'];

        return collect($types)
            ->map(fn ($type) => [
                'type' => $type,
                'email' => filter_var($settings->get("user.{$userId}.notify.{$type}.email", true), FILTER_VALIDATE_BOOLEAN),
                'push' => filter_var($settings->get("user.{$userId}.notify.{$type}.push", true), FILTER_VALIDATE_BOOLEAN),
            ])
            ->values()
            ->all();
    }
}
