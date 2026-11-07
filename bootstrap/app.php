<?php

use App\Http\Middleware\BlockIfInstalled;
use App\Http\Middleware\EnsureEntitlement;
use App\Http\Middleware\EnsureWorkspaceContext;
use App\Http\Middleware\LocalOnly;
use App\Http\Middleware\PastDueBanner;
use App\Http\Middleware\RedirectIfNotInstalled;
use App\Http\Middleware\RequireIdempotencyKey;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrialEndingNotice;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'entitlement' => EnsureEntitlement::class,
            'workspace.context' => EnsureWorkspaceContext::class,
            'idempotency' => RequireIdempotencyKey::class,
            // PROTOTYPE: allow the throwaway prototype route in local env only
            'proto.local' => LocalOnly::class,
            // First-run installer: /install* 404s once installed.lock exists
            'install.locked' => BlockIfInstalled::class,
        ]);
        $middleware->validateCsrfTokens(except: ['webhooks/*']);
        // Baseline security headers on every response (web + API)
        $middleware->append(SecurityHeaders::class);
        // Pre-install gate: everything redirects to /install until the lock file exists
        $middleware->prepend(RedirectIfNotInstalled::class);
        // Billing banners on authenticated dashboard pages (Phase 10)
        $middleware->web(append: [TrialEndingNotice::class, PastDueBanner::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // One error envelope for /api/v1: { message, code (stable snake_case), errors }.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage(),
                'code' => 'validation_failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Unauthenticated.',
                'code' => 'unauthenticated',
                'errors' => [],
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage() ?: 'Forbidden.',
                'code' => 'forbidden',
                'errors' => [],
            ], 403);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Resource not found.',
                'code' => 'not_found',
                'errors' => [],
            ], 404);
        });
    })->create();
