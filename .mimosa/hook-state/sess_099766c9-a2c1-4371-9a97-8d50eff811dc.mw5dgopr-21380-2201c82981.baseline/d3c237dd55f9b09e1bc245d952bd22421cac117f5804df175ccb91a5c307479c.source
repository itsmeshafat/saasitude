<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferOwnership
{
    public function execute(Workspace $workspace, User $currentOwner, int $newOwnerId): void
    {
        if (! $workspace->isOwnedBy($currentOwner)) {
            throw ValidationException::withMessages(['new_owner_id' => 'Only the current owner can transfer ownership.']);
        }

        if ($currentOwner->id === $newOwnerId) {
            throw ValidationException::withMessages(['new_owner_id' => 'New owner must be a different user.']);
        }

        DB::transaction(function () use ($workspace, $currentOwner, $newOwnerId): void {
            $workspace->update(['owner_id' => $newOwnerId]);

            WorkspaceMember::where('workspace_id', $workspace->id)
                ->where('user_id', $newOwnerId)
                ->update(['role' => 'owner']);

            WorkspaceMember::where('workspace_id', $workspace->id)
                ->where('user_id', $currentOwner->id)
                ->where('role', 'owner')
                ->update(['role' => 'admin']);

            TyroAudit::log('workspace.ownership_transferred', $workspace, ['owner_id' => $currentOwner->id], ['owner_id' => $newOwnerId]);
        });
    }
}
