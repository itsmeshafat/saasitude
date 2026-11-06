<?php

use App\Http\Controllers\Api\V1\BillingApiController;
use App\Http\Controllers\Api\V1\DataRightsApiController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use App\Http\Controllers\Api\V1\PlanApiController;
use App\Http\Controllers\Api\V1\WorkspaceApiController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

// Kept from scaffolding ( Sanctum-authenticated current user )
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Per-token sliding window ( keyed by token id when authenticated, IP otherwise )
RateLimiter::for('api-v1', function (Request $request) {
    $key = $request->user()?->currentAccessToken()?->id
        ?? $request->user()?->id
        ?? $request->ip();

    return [
        Limit::perMinute(60)->by('api-v1:'.$key),
        Limit::perMinute(240)->by('api-v1-ip:'.$request->ip()),
    ];
});

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:api-v1'])
    ->group(function (): void {
        // App config ( force-update / maintenance ) — read-only, no workspace context
        Route::get('app/config', [PlanApiController::class, 'appConfig']);

        // Me
        Route::get('me', [MeController::class, 'index'])->middleware('abilities:read');
        Route::patch('me', [MeController::class, 'update'])->middleware('abilities:write');
        Route::put('me/password', [MeController::class, 'updatePassword'])->middleware('abilities:write');
        Route::get('me/sessions', [MeController::class, 'sessions'])->middleware('abilities:read');
        Route::delete('me/sessions/{tokenId}', [MeController::class, 'destroySession'])->middleware('abilities:write');

        // Devices ( push tokens )
        Route::post('devices', [DeviceController::class, 'store'])->middleware('abilities:write');
        Route::delete('devices/{deviceId}', [DeviceController::class, 'destroy'])->middleware('abilities:write');

        // Notifications ( Phase 10 )
        Route::get('notifications', [NotificationApiController::class, 'index'])->middleware('abilities:read');
        Route::patch('notifications/{id}/read', [NotificationApiController::class, 'markRead'])->middleware('abilities:write');
        Route::get('notifications/preferences', [NotificationApiController::class, 'preferences'])->middleware('abilities:read');
        Route::patch('notifications/preferences', [NotificationApiController::class, 'updatePreferences'])->middleware('abilities:write');

        // Data rights ( P1: export + account deletion )
        Route::post('me/export', [DataRightsApiController::class, 'export'])
            ->middleware(['abilities:write', 'idempotency']);
        Route::delete('me', [DataRightsApiController::class, 'destroy'])
            ->middleware('abilities:write');

        // Workspaces ( own list/create — no workspace context needed )
        Route::get('workspaces', [WorkspaceApiController::class, 'index'])->middleware('abilities:read');
        Route::post('workspaces', [WorkspaceApiController::class, 'store'])->middleware('abilities:write');

        // Public plans ( with optional ?currency= price filter )
        Route::get('plans', [PlanApiController::class, 'index'])->middleware('abilities:read');

        // Workspace-scoped ( X-Workspace-Id header validated against membership )
        Route::prefix('workspaces/{workspaceId}')
            ->middleware('workspace.context')
            ->group(function (): void {
                Route::get('/', [WorkspaceApiController::class, 'show'])->middleware('abilities:read');
                Route::patch('/', [WorkspaceApiController::class, 'update'])->middleware('abilities:write');
                Route::get('members', [WorkspaceApiController::class, 'members'])->middleware('abilities:read');
                Route::post('members', [WorkspaceApiController::class, 'inviteMember'])->middleware('abilities:write');
                Route::delete('members/{memberId}', [WorkspaceApiController::class, 'removeMember'])->middleware('abilities:write');
                Route::delete('invitations/{invitationId}', [WorkspaceApiController::class, 'revokeInvitation'])->middleware('abilities:write');

                // Plans are public but live under the billing surface for convenience
                Route::get('subscription', [BillingApiController::class, 'subscription'])->middleware('ability:billing:read,read');
                Route::get('usage', [BillingApiController::class, 'usage'])->middleware('ability:billing:read,read');
                Route::get('invoices', [BillingApiController::class, 'invoices'])->middleware('ability:billing:read,read');
                Route::get('payments', [BillingApiController::class, 'payments'])->middleware('ability:billing:read,read');

                // Payment/subscription mutations require an Idempotency-Key header
                Route::post('checkout', [BillingApiController::class, 'checkout'])
                    ->middleware(['ability:billing:write,write', 'idempotency']);
                Route::post('subscription/cancel', [BillingApiController::class, 'cancel'])
                    ->middleware(['ability:billing:write,write', 'idempotency']);
                Route::post('subscription/resume', [BillingApiController::class, 'resume'])
                    ->middleware('abilities:billing:write,write');
                Route::post('subscription/change-plan', [BillingApiController::class, 'changePlan'])
                    ->middleware(['ability:billing:write,write', 'idempotency']);
            });
    });
