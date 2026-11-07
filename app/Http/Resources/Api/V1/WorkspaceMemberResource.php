<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property WorkspaceMember $resource
 * @property int $id
 * @property int $user_id
 * @property string|null $role
 * @property Carbon $created_at
 * @property-read User $user
 */
class WorkspaceMemberResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->whenLoaded('user', fn () => $this->user->name),
            'email' => $this->whenLoaded('user', fn () => $this->user->email),
            'role' => $this->role,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
