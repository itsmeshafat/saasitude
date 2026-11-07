<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Install\DemoDataSeeder;
use Illuminate\Console\Command;

/**
 * Removes the installer's demo dataset (users, workspaces,
 * subscriptions, payments, invoices, coupons, tickets) while leaving
 * real data and the seeded plans/features/settings untouched.
 */
class ResetDemoCommand extends Command
{
    protected $signature = 'saas:reset-demo';

    protected $description = 'Delete all installer demo data (keep real data and seeded plans)';

    public function handle(): int
    {
        $demoUserIds = User::whereIn('email', [
            ...DemoDataSeeder::DEMO_EMAILS,
            'trial@demo.com', 'pastdue@demo.com', 'canceled@demo.com',
        ])->pluck('id');

        $demoWorkspaceIds = Workspace::whereIn('owner_id', $demoUserIds)->pluck('id');

        $removed = 0;
        $removed += Invoice::whereIn('workspace_id', $demoWorkspaceIds)->delete();
        $removed += Payment::whereIn('workspace_id', $demoWorkspaceIds)->delete();
        $removed += Subscription::whereIn('workspace_id', $demoWorkspaceIds)->delete();
        $removed += SupportTicket::whereIn('number', DemoDataSeeder::DEMO_TICKET_NUMBERS)->delete();
        $removed += Coupon::whereIn('code', DemoDataSeeder::DEMO_COUPON_CODES)->delete();
        $removed += Workspace::whereIn('id', $demoWorkspaceIds)->delete(); // cascade removes members
        $removed += User::whereIn('id', $demoUserIds)->delete();

        $this->info("Demo data removed ({$removed} rows). Plans, settings and real data untouched.");

        return self::SUCCESS;
    }
}
