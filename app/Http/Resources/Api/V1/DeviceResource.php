<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property Device $resource
 * @property int $id
 * @property string|null $platform
 * @property string|null $app_version
 * @property Carbon|null $last_used_at
 * @property Carbon $created_at
 */
class DeviceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'app_version' => $this->app_version,
            'last_used_at' => $this->last_used_at->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
