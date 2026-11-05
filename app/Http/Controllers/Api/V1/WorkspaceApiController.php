<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateWorkspace;
use App\Actions\InviteMember;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WorkspaceMemberResource;
use App\Http\Resources\Api\V1\WorkspaceResource;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkspaceApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $workspaces = $request->user()->workspaces()
            ->with('workspaceMembers')
            ->orderBy('id')
            ->cursorPaginate(15);

        return WorkspaceResource::collection($workspaces);
    }

    public function store(Request $request, CreateWorkspace $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace = $action->execute($request->user(), $validated['name'], false);

        return (new WorkspaceResource($workspace->load('workspaceMembers')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request): WorkspaceResource
    {
        return new WorkspaceResource($request->attributes->get('workspace')->load('workspaceMembers'));
    }

    public function update(Request $request): WorkspaceResource
    {
        $this->authorizeWorkspaceRole($request, 'owner', 'admin');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
        ]);

        $workspace = $request->attributes->get('workspace');
        $workspace->update($validated);

        return new WorkspaceResource($workspace->fresh()->load('workspaceMembers'));
    }

    public function members(Request $request): AnonymousResourceCollection
    {
        $members = $request->attributes->get('workspace')
            ->workspaceMembers()
            ->with('user')
            ->orderBy('id')
            ->cursorPaginate(25);

        return WorkspaceMemberResource::collection($members);
    }

    public function inviteMember(Request $request, InviteMember $action): JsonResponse
    {
        $this->authorizeWorkspaceRole($request, 'owner', 'admin');

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,member,billing'],
        ]);

        $invitation = $action->execute(
            $request->attributes->get('workspace'),
            $validated['email'],
            $validated['role'],
        );

        return response()->json([
            'message' => 'Invitation sent.',
            'code' => 'invitation_sent',
            'errors' => [],
            'data' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'expires_at' => $invitation->expires_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function removeMember(Request $request, int $memberId): JsonResponse
    {
        $this->authorizeWorkspaceRole($request, 'owner', 'admin');

        $member = WorkspaceMember::query()
            ->where('workspace_id', $request->attributes->get('workspace')->id)
            ->whereKey($memberId)
            ->first();

        if ($member === null) {
            return response()->json([
                'message' => 'Member not found.',
                'code' => 'not_found',
                'errors' => [],
            ], 404);
        }

        if ($member->isOwner()) {
            return response()->json([
                'message' => 'Cannot remove the workspace owner.',
                'code' => 'owner_immutable',
                'errors' => [],
            ], 422);
        }

        $member->delete();

        return response()->json(['message' => 'Member removed.', 'code' => 'member_removed', 'errors' => []]);
    }

    public function revokeInvitation(Request $request, int $invitationId): JsonResponse
    {
        $this->authorizeWorkspaceRole($request, 'owner', 'admin');

        $deleted = WorkspaceInvitation::query()
            ->where('workspace_id', $request->attributes->get('workspace')->id)
            ->whereKey($invitationId)
            ->whereNull('accepted_at')
            ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'Invitation not found.',
                'code' => 'not_found',
                'errors' => [],
            ], 404);
        }

        return response()->json(['message' => 'Invitation revoked.', 'code' => 'invitation_revoked', 'errors' => []]);
    }

    /** @throws HttpException */
    private function authorizeWorkspaceRole(Request $request, string ...$roles): void
    {
        $member = $request->attributes->get('workspace')
            ->workspaceMembers()
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($member !== null && in_array($member->role, $roles, true), 403);
    }
}
