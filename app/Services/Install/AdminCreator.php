<?php

namespace App\Services\Install;

use App\Models\User;
use Database\Seeders\StaffRolesSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

/**
 * Step 3 — creates the first super-admin. Fires the same Registered
 * event as Tyro Login's registration flow so the personal workspace
 * is auto-created by WorkspaceServiceProvider (no forked auth logic).
 */
final class AdminCreator
{
    /**
     * @param  array{name: string, email: string, password: string}  $input
     */
    public function create(array $input): User
    {
        return DB::transaction(function () use ($input): User {
            // Roles must exist before StaffRolesSeeder attaches privileges.
            foreach (['admin', 'super-admin', 'user'] as $slug) {
                Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);
            }
            app(StaffRolesSeeder::class)->run();

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'], // 'hashed' cast
                'email_verified_at' => now(),
            ]);

            $user->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());

            // Registered event → WorkspaceServiceProvider → personal workspace.
            event(new Registered($user));

            return $user;
        });
    }
}
