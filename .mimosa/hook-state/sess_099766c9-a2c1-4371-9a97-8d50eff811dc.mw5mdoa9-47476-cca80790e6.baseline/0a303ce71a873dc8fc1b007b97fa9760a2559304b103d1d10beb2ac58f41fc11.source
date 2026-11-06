<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Actions\InviteMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_members_and_invite_form(): void
    {
        $owner = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');

        $this->actingAs($owner)
            ->get(route('dashboard.team'))
            ->assertOk()
            ->assertSee($owner->email)
            ->assertSee('Invite a member')
            ->assertSee('Send invitation');
    }

    public function test_plain_member_sees_no_manage_ui(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');
        $workspace->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        $this->actingAs($member)
            ->get(route('dashboard.team'))
            ->assertOk()
            ->assertDontSee('Invite a member')
            ->assertDontSee('Remove');
    }

    public function test_pending_invitations_are_listed(): void
    {
        $owner = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');

        app(InviteMember::class)->execute($workspace, 'newbie@example.com', 'member');

        $this->actingAs($owner)
            ->get(route('dashboard.team'))
            ->assertOk()
            ->assertSee('newbie@example.com')
            ->assertSee('Pending');
    }
}
