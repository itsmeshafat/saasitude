<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PlanResource;
use App\Models\Plan;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlanApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $plans = Plan::with(['prices', 'planFeatures.feature'])
            ->where('is_public', true)
            ->where('is_archived', false)
            ->orderBy('id')
            ->get();

        return PlanResource::collection($plans);
    }

    /** Mobile force-update / feature-flag config. */
    public function appConfig(SettingsService $settings): JsonResponse
    {
        return response()->json([
            'data' => [
                'min_app_version' => $settings->get('mobile.min_app_version', '1.0.0'),
                'maintenance' => (bool) $settings->get('system.maintenance_mode', false),
                'feature_flags' => [],
                'legal_urls' => [
                    'terms' => url('/terms'),
                    'privacy' => url('/privacy'),
                ],
            ],
        ]);
    }
}
