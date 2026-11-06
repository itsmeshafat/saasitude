<?php

namespace App\Actions;

use App\Mail\AccountDeletionConfirmed;
use App\Models\User;
use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class DeleteUserAccount
{
    /**
     * Delete a user account and all personal data. Guards:
     * - no active (paid) subscription on any owned workspace;
     * - no owned workspace with other members (transfer or remove first).
     * Personal workspaces (user is the only member) are deleted with the user
     * via FK cascades. Financial rows cascade with the workspace — retention
     * beyond deletion is a deployment concern (see MASTER.md notes).
     */
    public function execute(User $user): void
    {
        $owned = $user->workspaces()->wherePivot('role', 'owner')->get();

        foreach ($owned as $workspace) {
            $subscription = $workspace->activeSubscription();

            if ($subscription !== null && $subscription->isActive()) {
                throw ValidationException::withMessages([
                    'account' => 'Cancel the active subscription on "'.$workspace->name.'" before deleting your account.',
                ]);
            }

            if ($workspace->workspaceMembers()->where('user_id', '!=', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'account' => 'Transfer ownership of "'.$workspace->name.'" (it has other members) before deleting your account.',
                ]);
            }
        }

        // The confirmation email must go out while the account still exists.
        Mail::to($user->email)->queue(new AccountDeletionConfirmed($user));

        DB::transaction(function () use ($user): void {
            TyroAudit::log('account.deleted', $user, $user->only(['name', 'email']), null);
            $user->delete();
        });
    }
}
