<?php

namespace App\Services;

use App\Models\GatewayCustomer;
use App\Models\Workspace;

class GatewayCustomerResolver
{
    public function getOrCreate(Workspace $workspace, string $gateway): GatewayCustomer
    {
        return GatewayCustomer::firstOrCreate(
            ['workspace_id' => $workspace->id, 'gateway' => $gateway],
            ['gateway_customer_id' => ''] // filled by the gateway adapter before returning
        );
    }
}
