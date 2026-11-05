<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DeviceResource;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /** Register (or refresh) this device's push token. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'in:ios,android,web'],
            'app_version' => ['nullable', 'string', 'max:32'],
        ]);

        $device = Device::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'push_token' => $validated['push_token'],
            ],
            [
                'platform' => $validated['platform'],
                'app_version' => $validated['app_version'] ?? null,
                'last_used_at' => now(),
            ]
        );

        return (new DeviceResource($device))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, int $deviceId): JsonResponse
    {
        $deleted = Device::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($deviceId)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'Device not found.',
                'code' => 'not_found',
                'errors' => [],
            ], 404);
        }

        return response()->json(['message' => 'Device removed.', 'code' => 'device_removed', 'errors' => []]);
    }
}
