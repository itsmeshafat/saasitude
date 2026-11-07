<?php

namespace App\Http\Controllers;

use App\Actions\AcceptInvitation;
use App\Actions\InviteMember;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function show(Request $request): View
    {
        $workspace = $request->user()->currentWorkspace();

        $members = $workspace->workspaceMembers()->with('user')->get();
        $invitations = $workspace->invitations()
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->get();

        return view('dashboard.team', compact('workspace', 'members', 'invitations'));
    }

    public function invite(Request $request, InviteMember $action): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,member,billing'],
        ]);

        $workspace = $request->user()->currentWorkspace();

        $action->execute($workspace, $validated['email'], $validated['role']);

        return redirect()->route('dashboard.team')->with('success', 'Invitation sent.');
    }

    public function remove(Request $request, WorkspaceMember $member): RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();

        abort_unless($workspace->id === $member->workspace_id, 403);
        abort_if($member->isOwner(), 403, 'Cannot remove the workspace owner.');

        $member->delete();

        return redirect()->route('dashboard.team')->with('success', 'Member removed.');
    }

    public function accept(Request $request, string $token, AcceptInvitation $action): RedirectResponse
    {
        $invitation = WorkspaceInvitation::where('token_hash', hash('sha256', $token))
            ->firstOrFail();

        $action->execute($invitation, $request->user());

        return redirect()->route('dashboard.team')->with('success', 'You have joined the workspace.');
    }
}
