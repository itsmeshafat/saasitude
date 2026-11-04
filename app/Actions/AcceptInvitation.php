<?php

namespace App\Actions;

use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptInvitation
{
    public function execute(WorkspaceInvitation $invitation, ?User $user): void
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages(['token' => 'This invitation is expired or already used.']);
        }

        $user ??= throw ValidationException::withMessages(['token' => 'You must be logged in to accept an invitation.']);

        if ($user->email !== $invitation->email) {
            throw ValidationException::withMessages(['token' => 'This invitation was sent to a different email address.']);
        }

        DB::transaction(function () use ($invitation, $user): void {
            WorkspaceMember::firstOrCreate(
                ['workspace_id' => $invitation->workspace_id, 'user_id' => $user->id],
                ['role' => $invitation->role, 'joined_at' => now()]
            );

            $invitation->update(['accepted_at' => now()]);
        });
    }
}
