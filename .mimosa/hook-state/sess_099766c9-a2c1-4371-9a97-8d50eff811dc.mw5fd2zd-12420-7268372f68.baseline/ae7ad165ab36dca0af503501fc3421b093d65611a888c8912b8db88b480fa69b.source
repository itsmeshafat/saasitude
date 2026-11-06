<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Workspace;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EntitlementService
{
    public function for(Workspace $workspace): EntitlementContext
    {
        $features = Cache::store('array')->rememberForever(
            "entitlements_workspace_{$workspace->id}",
            fn () => $this->resolveFeatures($workspace)
        );

        return new EntitlementContext($features);
    }

    public function flush(int $workspaceId): void
    {
        Cache::store('array')->forget("entitlements_workspace_{$workspaceId}");
    }

    private function resolveFeatures(Workspace $workspace): array
    {
        $plan = $this->resolveActivePlan($workspace);

        if ($plan === null) {
            return [];
        }

        return $plan->planFeatures()
            ->with('feature')
            ->get()
            ->keyBy(fn ($pf) => $pf->feature->key)
            ->map(fn ($pf) => ['type' => $pf->feature->type, 'value' => $pf->value])
            ->all();
    }

    private function resolveActivePlan(Workspace $workspace): ?Plan
    {
        // Phase 4+: resolve from active subscription
        if (Schema::hasTable('subscriptions')) {
            $sub = DB::table('subscriptions')
                ->where('workspace_id', $workspace->id)
                ->whereIn('status', ['active', 'trialing'])
                ->first();

            if ($sub) {
                return Plan::find($sub->plan_id);
            }
        }

        return Plan::where('slug', 'free')->first();
    }
}
