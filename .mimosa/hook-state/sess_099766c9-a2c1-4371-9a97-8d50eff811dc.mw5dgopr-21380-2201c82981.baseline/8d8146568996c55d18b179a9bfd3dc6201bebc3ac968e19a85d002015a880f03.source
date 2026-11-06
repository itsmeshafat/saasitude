<?php

namespace App\Actions;

use App\Models\Workspace;
use HasinHayder\Tyro\Support\TyroAudit;

class UpdateWorkspace
{
    public function execute(Workspace $workspace, array $data): Workspace
    {
        $old = $workspace->only(array_keys($data));

        $workspace->update($data);

        TyroAudit::log('workspace.updated', $workspace, $old, $data);

        return $workspace->fresh();
    }
}
