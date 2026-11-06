<?php

namespace App\Http\Middleware;

use App\Exceptions\EntitlementException;
use App\Services\EntitlementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEntitlement
{
    public function __construct(private readonly EntitlementService $entitlements) {}

    public function handle(Request $request, Closure $next, string $key): Response
    {
        $workspace = $request->user()?->currentWorkspace();

        if (! $workspace) {
            abort(403);
        }

        try {
            $this->entitlements->for($workspace)->enforce($key);
        } catch (EntitlementException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'plan_limit_reached',
                    'feature' => $e->getFeatureKey(),
                    'message' => $e->getMessage(),
                ], 402);
            }

            return redirect()->route('dashboard.plans')
                ->with('error', $e->getMessage());
        }

        return $next($request);
    }
}
