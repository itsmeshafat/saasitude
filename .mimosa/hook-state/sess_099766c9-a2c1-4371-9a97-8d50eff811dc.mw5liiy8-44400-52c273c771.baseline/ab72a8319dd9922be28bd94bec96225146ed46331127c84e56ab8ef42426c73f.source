<?php

namespace Tests\Feature;

use App\Actions\AcceptInvitation;
use App\Actions\CreateWorkspace;
use App\Actions\InviteMember;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkspaceInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invite_creates_pending_invitation(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');

        app(InviteMember::class)->execute($workspace, 'alice@example.com', 'member');

        $this->assertDatabaseHas('workspace_invitations', [
            'workspace_id' => $workspace->id,
            'email' => 'alice@example.com',
            'role' => 'member',
            'accepted_at' => null,
        ]);
    }

    public function test_accept_invitation_adds_member(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'alice@example.com']);
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');

        $invitation = app(InviteMember::class)->execute($workspace, 'alice@example.com', 'member');

        app(AcceptInvitation::class)->execute($invitation, $invitee);

        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $invitee->id,
            'role' => 'member',
        ]);

        $this->assertNotNull($invitation->fresh()->accepted_at);
    }

    public function test_accept_expired_invitation_throws(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'bob@example.com']);
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');

        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'email' => 'bob@example.com',
            'role' => 'member',
            'token_hash' => hash('sha256', 'expired_token'),
            'expires_at' => now()->subDay(),
        ]);

        $this->expectException(ValidationException::class);

        app(AcceptInvitation::class)->execute($invitation, $invitee);
    }

    public function test_accept_invitation_wrong_email_throws(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');

        $invitation = app(InviteMember::class)->execute($workspace, 'alice@example.com', 'member');

        $this->expectException(ValidationException::class);

        app(AcceptInvitation::class)->execute($invitation, $wrongUser);
    }
}
