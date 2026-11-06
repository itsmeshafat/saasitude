<?php

namespace App\Http\Controllers;

use App\Actions\TransferOwnership;
use App\Actions\UpdateWorkspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceSettingsController extends Controller
{
    public function show(Request $request): View
    {
        $workspace = $request->user()->currentWorkspace();

        return view('dashboard.workspace-settings', compact('workspace'));
    }

    public function update(Request $request, UpdateWorkspace $action): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace = $request->user()->currentWorkspace();

        $action->execute($workspace, $validated);

        return redirect()->route('dashboard.workspace-settings')
            ->with('success', 'Workspace updated.');
    }

    public function transferOwnership(Request $request, TransferOwnership $action): RedirectResponse
    {
        $validated = $request->validate([
            'new_owner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $workspace = $request->user()->currentWorkspace();

        $action->execute($workspace, $request->user(), (int) $validated['new_owner_id']);

        return redirect()->route('dashboard.workspace-settings')
            ->with('success', 'Ownership transferred.');
    }
}
