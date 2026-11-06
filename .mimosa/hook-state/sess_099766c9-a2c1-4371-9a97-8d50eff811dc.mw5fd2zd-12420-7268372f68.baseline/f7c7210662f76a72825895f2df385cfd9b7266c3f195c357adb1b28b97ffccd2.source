<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Actions\TransferOwnership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkspaceMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_ownership_updates_owner_id(): void
    {
        $owner = User::factory()->create();
        $newOwner = User::factory()->create();

        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');
        $workspace->members()->attach($newOwner->id, ['role' => 'admin', 'joined_at' => now()]);

        app(TransferOwnership::class)->execute($workspace, $owner, $newOwner->id);

        $this->assertDatabaseHas('workspaces', ['id' => $workspace->id, 'owner_id' => $newOwner->id]);
        $this->assertDatabaseHas('workspace_members', ['user_id' => $newOwner->id, 'role' => 'owner']);
        $this->assertDatabaseHas('workspace_members', ['user_id' => $owner->id, 'role' => 'admin']);
    }

    public function test_transfer_ownership_rejects_non_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');

        $this->expectException(ValidationException::class);

        app(TransferOwnership::class)->execute($workspace, $other, $owner->id);
    }

    public function test_transfer_ownership_rejects_self(): void
    {
        $owner = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->execute($owner, 'Team');

        $this->expectException(ValidationException::class);

        app(TransferOwnership::class)->execute($workspace, $owner, $owner->id);
    }
}
