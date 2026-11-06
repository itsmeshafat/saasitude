<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires an Idempotency-Key header on POSTs that create payments or
 * subscriptions, so mobile retries cannot double-charge.
 */
class RequireIdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->header('Idempotency-Key')) {
            return response()->json([
                'message' => 'The Idempotency-Key header is required for this request.',
                'code' => 'idempotency_key_required',
                'errors' => [],
            ], 422);
        }

        return $next($request);
    }
}
