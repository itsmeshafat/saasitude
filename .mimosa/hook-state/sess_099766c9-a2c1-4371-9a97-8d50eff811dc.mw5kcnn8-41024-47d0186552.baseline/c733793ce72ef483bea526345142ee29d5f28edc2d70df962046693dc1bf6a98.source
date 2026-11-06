<?php

namespace Database\Seeders;

use HasinHayder\Tyro\Models\Privilege;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class StaffRolesSeeder extends Seeder
{
    public function run(): void
    {
        $newRoles = [
            ['name' => 'Support', 'slug' => 'support'],
            ['name' => 'Finance', 'slug' => 'finance'],
        ];

        foreach ($newRoles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $definitions = [
            ['name' => 'View Billing', 'slug' => 'billing.view', 'description' => 'View invoices and payment history.', 'roles' => ['admin', 'super-admin', 'finance', 'support']],
            ['name' => 'Refund Billing', 'slug' => 'billing.refund', 'description' => 'Issue refunds on payments.', 'roles' => ['super-admin', 'finance']],
            ['name' => 'Manage Plans', 'slug' => 'plans.manage', 'description' => 'Create and edit subscription plans.', 'roles' => ['admin', 'super-admin']],
            ['name' => 'Manage Coupons', 'slug' => 'coupons.manage', 'description' => 'Create and expire discount coupons.', 'roles' => ['admin', 'super-admin']],
            ['name' => 'Manage Workspaces', 'slug' => 'workspaces.manage', 'description' => 'Suspend, restore and inspect workspaces.', 'roles' => ['admin', 'super-admin', 'support']],
            ['name' => 'Replay Webhooks', 'slug' => 'webhooks.replay', 'description' => 'Re-queue failed webhook events.', 'roles' => ['admin', 'super-admin']],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'description' => 'Edit application-level settings.', 'roles' => ['super-admin']],
            ['name' => 'Reply to Support', 'slug' => 'support.reply', 'description' => 'Reply to and close support tickets.', 'roles' => ['admin', 'super-admin', 'support']],
            ['name' => 'Impersonate Users', 'slug' => 'impersonate', 'description' => 'Log in as any user for debugging.', 'roles' => ['super-admin']],
            ['name' => 'View Audit Log', 'slug' => 'audit.view', 'description' => 'Read the system audit trail.', 'roles' => ['admin', 'super-admin']],
        ];

        $allRoleSlugs = collect($definitions)->flatMap(fn ($d) => $d['roles'])->unique()->all();
        $roleMap = Role::whereIn('slug', $allRoleSlugs)->get()->keyBy('slug');

        foreach ($definitions as $definition) {
            $privilege = Privilege::updateOrCreate(
                ['slug' => $definition['slug']],
                Arr::only($definition, ['name', 'description'])
            );

            $roleIds = collect($definition['roles'])
                ->map(fn ($slug) => $roleMap->get($slug)?->id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (! empty($roleIds)) {
                $privilege->roles()->syncWithoutDetaching($roleIds);
            }
        }
    }
}
