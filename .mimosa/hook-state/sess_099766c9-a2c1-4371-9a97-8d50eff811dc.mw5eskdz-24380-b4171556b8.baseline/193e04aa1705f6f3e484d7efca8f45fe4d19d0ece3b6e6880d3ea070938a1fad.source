<?php

use App\Actions\SwitchWorkspace;
use App\Http\Controllers\Admin\AdminOverviewController;
use App\Http\Controllers\Admin\AdminPaymentsController;
use App\Http\Controllers\Admin\AdminSubscriptionsController;
use App\Http\Controllers\Admin\AdminSupportInboxController;
use App\Http\Controllers\Admin\AdminWorkspacesController;
use App\Http\Controllers\Admin\EmailTemplatesController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\GatewaySettingsController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\WebhookEventsController;
use App\Http\Controllers\AdminInvoiceController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BillingOverviewController;
use App\Http\Controllers\CancelSubscriptionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PaddleWebhookController;
use App\Http\Controllers\PaymentMethodsController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\Public\ChangelogController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\LegalController;
use App\Http\Controllers\Public\SiteController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\SSLCommerzWebhookController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WorkspaceSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// PROTOTYPE (throwaway) — Phase 11 public-site visual directions. Local only.
Route::view('/prototype/public-site', 'prototype.public-site')->middleware('proto.local');

// Public site (Phase 11)
Route::get('/', [SiteController::class, 'home'])->name('public.home');
Route::get('pricing', [SiteController::class, 'pricing'])->name('public.pricing');
Route::get('contact', [ContactController::class, 'show'])->name('public.contact');
Route::post('contact', [ContactController::class, 'submit'])->middleware('throttle:6,1')->name('public.contact.submit');
Route::get('changelog', [ChangelogController::class, 'index'])->name('public.changelog');
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap');
Route::get('{page}', [LegalController::class, 'show'])->whereIn('page', ['terms', 'privacy', 'refund-policy'])->name('public.legal');

// Webhook endpoints — excluded from CSRF via bootstrap/app.php
Route::post('webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::post('webhooks/paddle', [PaddleWebhookController::class, 'handle'])->name('webhooks.paddle');
Route::post('webhooks/sslcommerz', [SSLCommerzWebhookController::class, 'handle'])->name('webhooks.sslcommerz');

Route::middleware(['auth'])->group(function (): void {
    // Role-aware landing: admins -> analytics, users -> billing overview.
    // Named tyro-dashboard.index so package internals (e.g. EnsureIsAdmin's
    // non-admin redirect) resolve to this role-aware entry point.
    Route::get('dashboard', function (Request $request) {
        $user = $request->user();

        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super-admin', 'finance', 'support'])) {
            return redirect()->route('dashboard.overview');
        }

        return redirect()->route('dashboard.billing');
    })->name('tyro-dashboard.index');

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
    Route::get('dashboard/billing/invoices', [InvoiceController::class, 'index'])->name('dashboard.billing.invoices');
    Route::get('dashboard/billing/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('dashboard.billing.invoices.download');

    // Admin billing
    Route::get('dashboard/admin/billing/invoices', [AdminInvoiceController::class, 'index'])->name('admin.billing.invoices');
    Route::get('dashboard/admin/billing/invoices/{invoice}/download', [AdminInvoiceController::class, 'download'])->name('admin.billing.invoices.download');
    Route::post('dashboard/admin/billing/invoices/{invoice}/regenerate', [AdminInvoiceController::class, 'regenerate'])->name('admin.billing.invoices.regenerate');

    Route::post('dashboard/workspace/switch', function (Request $request, SwitchWorkspace $action) {
        $action->execute($request->user(), (int) $request->input('workspace_id'));

        return redirect()->back();
    })->name('dashboard.workspace.switch');

    // User billing screens (Phase 7)
    Route::get('dashboard/billing', [BillingOverviewController::class, 'show'])->name('dashboard.billing');
    Route::get('dashboard/billing/plans', [PlansController::class, 'show'])->name('dashboard.plans');
    Route::get('dashboard/billing/payment-methods', [PaymentMethodsController::class, 'show'])->name('dashboard.payment-methods');
    Route::get('dashboard/billing/cancel-subscription', [CancelSubscriptionController::class, 'show'])->name('dashboard.cancel-subscription');
    Route::post('dashboard/billing/cancel-subscription', [CancelSubscriptionController::class, 'cancel'])->name('dashboard.cancel-subscription.cancel');

    // Notifications (Phase 10)
    Route::get('dashboard/notifications', [NotificationsController::class, 'index'])->name('dashboard.notifications');
    Route::get('dashboard/notifications/poll', [NotificationsController::class, 'poll'])->name('dashboard.notifications.poll');
    Route::post('dashboard/notifications/{id}/read', [NotificationsController::class, 'markRead'])->name('dashboard.notifications.read');
    Route::post('dashboard/notifications/read-all', [NotificationsController::class, 'markAllRead'])->name('dashboard.notifications.read-all');
    Route::post('dashboard/notifications/preferences', [NotificationsController::class, 'updatePreferences'])->name('dashboard.notifications.preferences');

    // Support tickets (Phase 10)
    Route::get('dashboard/support', [SupportController::class, 'index'])->name('dashboard.support');
    Route::post('dashboard/support', [SupportController::class, 'store'])->name('dashboard.support.store');
    Route::get('dashboard/support/{ticket}', [SupportController::class, 'show'])->name('dashboard.support.show');
    Route::post('dashboard/support/{ticket}/reply', [SupportController::class, 'reply'])->name('dashboard.support.reply');
});

// Admin console (Phase 8)
Route::middleware(['auth', 'tyro-dashboard.admin'])->group(function (): void {
    Route::get('dashboard/overview', [AdminOverviewController::class, 'show'])->name('dashboard.overview');

    // Exports (reports + CSV)
    Route::get('dashboard/admin/exports/report', [ExportController::class, 'report'])->name('admin.exports.report');
    Route::get('dashboard/admin/exports/{type}', [ExportController::class, 'csv'])
        ->whereIn('type', ['payments', 'invoices', 'subscriptions', 'workspaces'])
        ->name('admin.exports.csv');
    Route::get('dashboard/workspaces-admin', [AdminWorkspacesController::class, 'index'])->name('admin.workspaces.index');
    Route::get('dashboard/workspaces-admin/{workspace}', [AdminWorkspacesController::class, 'show'])->name('admin.workspaces.show');
    Route::post('dashboard/workspaces-admin/{workspace}/suspend', [AdminWorkspacesController::class, 'suspend'])->name('admin.workspaces.suspend');
    Route::post('dashboard/workspaces-admin/{workspace}/restore', [AdminWorkspacesController::class, 'restore'])->name('admin.workspaces.restore');
    Route::post('dashboard/workspaces-admin/{workspace}/force-plan', [AdminWorkspacesController::class, 'forcePlan'])->name('admin.workspaces.force-plan');
    Route::get('dashboard/subscriptions-admin', [AdminSubscriptionsController::class, 'index'])->name('admin.subscriptions.index');
    Route::get('dashboard/subscriptions-admin/{subscription}', [AdminSubscriptionsController::class, 'show'])->name('admin.subscriptions.show');
    Route::get('dashboard/payments-admin', [AdminPaymentsController::class, 'index'])->name('admin.payments.index');
    Route::post('dashboard/payments-admin/{payment}/refund', [AdminPaymentsController::class, 'refund'])->name('admin.payments.refund');
    Route::get('dashboard/webhook-events', [WebhookEventsController::class, 'index'])->name('admin.webhook-events.index');
    Route::get('dashboard/webhook-events/{event}', [WebhookEventsController::class, 'show'])->name('admin.webhook-events.show');
    Route::post('dashboard/webhook-events/{event}/replay', [WebhookEventsController::class, 'replay'])->name('admin.webhook-events.replay');
    Route::get('dashboard/gateway-settings', [GatewaySettingsController::class, 'show'])->name('admin.gateway-settings');
    Route::post('dashboard/gateway-settings', [GatewaySettingsController::class, 'update'])->name('admin.gateway-settings.update');
    Route::get('dashboard/system-health', [SystemHealthController::class, 'show'])->name('admin.system-health');
});

// Admin support inbox (Phase 10)
Route::middleware(['auth', 'tyro-dashboard.admin'])->group(function (): void {
    Route::get('dashboard/support-inbox', [AdminSupportInboxController::class, 'index'])->name('admin.support.index');
    Route::get('dashboard/support-inbox/{ticket}', [AdminSupportInboxController::class, 'show'])->name('admin.support.show');
    Route::post('dashboard/support-inbox/{ticket}/reply', [AdminSupportInboxController::class, 'reply'])->name('admin.support.reply');
    Route::post('dashboard/support-inbox/{ticket}/close', [AdminSupportInboxController::class, 'close'])->name('admin.support.close');
});

// Email templates admin preview (Phase 10)
Route::middleware(['auth', 'tyro-dashboard.admin'])->group(function (): void {
    Route::get('dashboard/email-templates', [EmailTemplatesController::class, 'index'])->name('admin.email-templates');
    Route::get('dashboard/email-templates/{key}', [EmailTemplatesController::class, 'show'])->name('admin.email-templates.show');
    Route::post('dashboard/email-templates/{key}/send-test', [EmailTemplatesController::class, 'sendTest'])->name('admin.email-templates.send-test');
});
