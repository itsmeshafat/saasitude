<?php

namespace App\Http\Controllers;

use App\Actions\DeleteUserAccount;
use App\Jobs\ProcessDataExport;
use App\Models\DataExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DataPrivacyController extends Controller
{
    public function show(Request $request): View
    {
        return view('dashboard.data-privacy', [
            'exports' => $request->user()->dataExports()->latest('id')->get(),
        ]);
    }

    /** Queue a fresh export of everything we hold for this user. */
    public function startExport(Request $request): RedirectResponse
    {
        $export = $request->user()->dataExports()->create([
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        ProcessDataExport::dispatch($export->id);

        return redirect()->route('dashboard.data-privacy')
            ->with('success', 'Export queued — you will receive an email when it is ready (usually under a minute).');
    }

    /** Authorised download: owner only, unexpired, single-use tracking. */
    public function download(Request $request, DataExport $export)
    {
        abort_unless($export->user_id === $request->user()->id, 403);
        abort_unless($export->isReady(), 404, 'Export is not available (expired or still processing).');

        $export->update(['downloaded_at' => now()]);

        return Storage::download($export->file_path, 'my-data-'.now()->format('Ymd').'.json');
    }

    /** Delete the account (password-confirmed). See DeleteUserAccount guards. */
    public function deleteAccount(Request $request, DeleteUserAccount $action): RedirectResponse
    {
        $validated = $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'current_password:web'],
        ]);

        $user = $request->user();

        // Logout BEFORE the delete: Laravel's logout() cycles the remember
        // token and saves the user — doing that after the delete would
        // re-INSERT the deleted account.
        auth()->logout();

        $action->execute($user);

        return redirect()->route('public.home')
            ->with('success', 'Your account has been deleted. A confirmation email has been sent.');
    }
}
