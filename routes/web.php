<?php

use App\Actions\SwitchWorkspace;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WorkspaceSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Webhook endpoints — excluded from CSRF via bootstrap/app.php
Route::post('webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');

Route::middleware(['auth'])->group(function (): void {
    Route::get('dashboard/workspace-settings', [WorkspaceSettingsController::class, 'show'])->name('dashboard.workspace-settings');
    Route::put('dashboard/workspace-settings', [WorkspaceSettingsController::class, 'update'])->name('dashboard.workspace-settings.update');
    Route::post('dashboard/workspace-settings/transfer', [WorkspaceSettingsController::class, 'transferOwnership'])->name('dashboard.workspace-settings.transfer');

    Route::get('dashboard/team', [TeamController::class, 'show'])->name('dashboard.team');
    Route::post('dashboard/team/invite', [TeamController::class, 'invite'])->name('dashboard.team.invite');
    Route::delete('dashboard/team/{member}', [TeamController::class, 'remove'])->name('dashboard.team.remove');
    Route::get('dashboard/team/accept/{token}', [TeamController::class, 'accept'])->name('dashboard.team.accept');

    // Billing (T-010, T-012)
    Route::get('dashboard/billing/checkout', [BillingController::class, 'checkout'])->name('dashboard.billing.checkout');
    Route::post('dashboard/billing/checkout', [BillingController::class, 'startCheckout'])->name('dashboard.billing.checkout.start');
    Route::get('dashboard/billing/success', [BillingController::class, 'success'])->name('dashboard.billing.success');
    Route::get('dashboard/billing/cancel', [BillingController::class, 'cancel'])->name('dashboard.billing.cancel');
    Route::get('dashboard/billing/portal', [BillingController::class, 'portal'])->name('dashboard.billing.portal');

    Route::post('dashboard/workspace/switch', function (Request $request, SwitchWorkspace $action) {
        $action->execute($request->user(), (int) $request->input('workspace_id'));

        return redirect()->back();
    })->name('dashboard.workspace.switch');
});
