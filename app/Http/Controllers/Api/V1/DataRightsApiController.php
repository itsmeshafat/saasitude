<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\DeleteUserAccount;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessDataExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DataRightsApiController extends Controller
{
    /** Queue a data export (docs/06-api.md P1: POST /me/export). */
    public function export(Request $request): JsonResponse
    {
        $export = $request->user()->dataExports()->create([
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        ProcessDataExport::dispatch($export->id);

        return response()->json([
            'message' => 'Export queued — you will be notified when it is ready.',
            'code' => 'export_queued',
            'errors' => [],
            'data' => ['export_id' => $export->id],
        ], 202);
    }

    /** Delete the account (docs/06-api.md P1: DELETE /me). Password-confirmed. */
    public function destroy(Request $request, DeleteUserAccount $action): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Manual hash check: `current_password:web` can't resolve the user on
        // a Sanctum-token request (the web guard has no session user).
        if (! Hash::check((string) $request->input('password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('The password is incorrect.')],
            ]);
        }

        $user = $request->user();

        $action->execute($user);

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Your account has been deleted.',
            'code' => 'account_deleted',
            'errors' => [],
        ]);
    }
}
