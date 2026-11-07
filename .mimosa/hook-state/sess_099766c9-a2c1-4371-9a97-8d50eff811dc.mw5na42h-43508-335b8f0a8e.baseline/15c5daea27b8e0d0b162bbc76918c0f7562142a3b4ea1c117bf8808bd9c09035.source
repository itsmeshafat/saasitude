<?php

namespace App\Actions;

use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Notifications\InvitationSentNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteMember
{
    public function execute(Workspace $workspace, string $email, string $role): WorkspaceInvitation
    {
        $token = Str::random(64);

        $invitation = WorkspaceInvitation::updateOrCreate(
            ['workspace_id' => $workspace->id, 'email' => $email],
            [
                'role' => $role,
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ]
        );

        // ponytail: notification is a stub; wire real mail in T-021
        Notification::route('mail', $email)
            ->notify(new InvitationSentNotification($workspace, $token));

        return $invitation;
    }
}
