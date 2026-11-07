<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Validation\ValidationException;

class SwitchWorkspace
{
    public function execute(User $user, int $workspaceId): Workspace
    {
        $workspace = $user->workspaces()->find($workspaceId);

        if (! $workspace) {
            throw ValidationException::withMessages(['workspace_id' => 'You do not belong to that workspace.']);
        }

        session(['current_workspace_id' => $workspace->id]);

        return $workspace;
    }
}
