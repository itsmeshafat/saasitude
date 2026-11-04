<?php

use App\Actions\SwitchWorkspace;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WorkspaceSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('dashboard/workspace-settings', [WorkspaceSettingsController::class, 'show'])->name('dashboard.workspace-settings');
    Route::put('dashboard/workspace-settings', [WorkspaceSettingsController::class, 'update'])->name('dashboard.workspace-settings.update');
    Route::post('dashboard/workspace-settings/transfer', [WorkspaceSettingsController::class, 'transferOwnership'])->name('dashboard.workspace-settings.transfer');

    Route::get('dashboard/team', [TeamController::class, 'show'])->name('dashboard.team');
    Route::post('dashboard/team/invite', [TeamController::class, 'invite'])->name('dashboard.team.invite');
    Route::delete('dashboard/team/{member}', [TeamController::class, 'remove'])->name('dashboard.team.remove');
    Route::get('dashboard/team/accept/{token}', [TeamController::class, 'accept'])->name('dashboard.team.accept');

    Route::post('dashboard/workspace/switch', function (Request $request, SwitchWorkspace $action) {
        $action->execute($request->user(), (int) $request->input('workspace_id'));

        return redirect()->back();
    })->name('dashboard.workspace.switch');
});
