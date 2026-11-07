<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateWorkspace
{
    public function execute(User $user, string $name, bool $isPersonal = false): Workspace
    {
        return DB::transaction(function () use ($user, $name, $isPersonal): Workspace {
            $workspace = Workspace::create([
                'owner_id' => $user->id,
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'is_personal' => $isPersonal,
            ]);

            WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            TyroAudit::log('workspace.created', $workspace, null, $workspace->toArray());

            return $workspace;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $n = 1;

        while (Workspace::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$n}";
            $n++;
        }

        return $slug;
    }
}
