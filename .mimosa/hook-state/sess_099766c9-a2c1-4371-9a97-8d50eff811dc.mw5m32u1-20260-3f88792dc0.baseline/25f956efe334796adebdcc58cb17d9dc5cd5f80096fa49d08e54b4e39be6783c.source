<?php

namespace Tests\Feature;

use App\Actions\CreateWorkspace;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_workspace_with_owner_member(): void
    {
        $user = User::factory()->create();

        $workspace = app(CreateWorkspace::class)->execute($user, 'Acme Corp');

        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertSame('Acme Corp', $workspace->name);
        $this->assertSame($user->id, $workspace->owner_id);

        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_generates_unique_slug(): void
    {
        $user = User::factory()->create();
        $action = app(CreateWorkspace::class);

        $first = $action->execute($user, 'Acme');
        $second = $action->execute($user, 'Acme');

        $this->assertNotSame($first->slug, $second->slug);
    }

    public function test_personal_workspace_flag(): void
    {
        $user = User::factory()->create();

        $workspace = app(CreateWorkspace::class)->execute($user, $user->name, true);

        $this->assertTrue($workspace->is_personal);
    }

    public function test_registered_event_creates_workspace_automatically(): void
    {
        $user = User::factory()->create();

        event(new Registered($user));

        $this->assertDatabaseHas('workspaces', ['owner_id' => $user->id, 'is_personal' => true]);
        $this->assertDatabaseHas('workspace_members', ['user_id' => $user->id, 'role' => 'owner']);
    }
}
