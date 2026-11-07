<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanPrice;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResourceIconTest extends TestCase
{
    public static function resourceModels(): array
    {
        return [
            'plans' => [Plan::class],
            'plan prices' => [PlanPrice::class],
            'features' => [Feature::class],
            'coupons' => [Coupon::class],
        ];
    }

    #[DataProvider('resourceModels')]
    public function test_resource_config_carries_sidebar_icon(string $modelClass): void
    {
        $config = $modelClass::getResourceConfig();

        $this->assertArrayHasKey('icon', $config);
        $this->assertStringContainsString('<svg', $config['icon']);
        $this->assertStringContainsString('currentColor', $config['icon']);
        // HasCrud base config must survive the merge.
        $this->assertArrayHasKey('fields', $config);
        $this->assertSame($modelClass, $config['model']);
    }
}
