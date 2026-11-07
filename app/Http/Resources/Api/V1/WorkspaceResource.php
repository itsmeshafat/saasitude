<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property Workspace $resource
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property bool $is_personal
 * @property Carbon $created_at
 * @property-read Collection<int, WorkspaceMember> $workspaceMembers
 */
class WorkspaceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_personal' => $this->is_personal,
            'is_suspended' => $this->resource->isSuspended(),
            'role' => $this->whenLoaded('workspaceMembers', fn () => $this->workspaceMembers
                ->firstWhere('user_id', $request->user()->id)?->role),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
