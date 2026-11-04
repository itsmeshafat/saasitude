<?php

namespace App\Facades;

use App\Models\Workspace;
use App\Services\EntitlementContext;
use App\Services\EntitlementService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static EntitlementContext for(Workspace $workspace)
 * @method static void flush(int $workspaceId)
 */
class Entitlements extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EntitlementService::class;
    }
}
