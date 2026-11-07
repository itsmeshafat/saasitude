<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;

class MeController extends Controller
{
    public function index(Request $request): JsonResource
    {
        return new JsonResource([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'email_verified' => $request->user()->email_verified_at !== null,
            'created_at' => $request->user()->created_at?->toIso8601String(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
        ]);

        $request->user()->update($validated);

        return $this->index($request)->response()->setStatusCode(200);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'code' => 'invalid_current_password',
                'errors' => [],
            ], 422);
        }

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Password updated.', 'code' => 'password_updated', 'errors' => []]);
    }

    /** Active API tokens (sessions). */
    public function sessions(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->tokens()->latest('last_used_at')->get(['id', 'name', 'last_used_at', 'created_at'])->map(
                fn ($token) => [
                    'id' => $token->id,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at?->toIso8601String(),
                    'created_at' => $token->created_at?->toIso8601String(),
                ]
            ),
        ]);
    }

    public function destroySession(Request $request, int $tokenId): JsonResponse
    {
        $deleted = $request->user()->tokens()->whereKey($tokenId)->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'Session not found.',
                'code' => 'not_found',
                'errors' => [],
            ], 404);
        }

        return response()->json(['message' => 'Session revoked.', 'code' => 'session_revoked', 'errors' => []]);
    }
}
