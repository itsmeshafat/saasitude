<?php

namespace App\Services\Install;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\Workspace;
use Database\Factories\InvoiceFactory;
use Database\Factories\PaymentFactory;
use Database\Factories\SubscriptionFactory;
use Database\Factories\SupportTicketFactory;
use Database\Factories\WorkspaceFactory;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\PlansAndFeaturesSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Step 5 — core seeders always; optional rich demo dataset that makes
 * every admin/user screen explorable. Idempotent. Removable via
 * `php artisan saas:reset-demo`.
 */
final class DemoDataSeeder
{
    public const DEMO_OWNER_EMAIL = 'demo@demo.com';

    public const DEMO_MEMBER_EMAIL = 'teammate@demo.com';

    public const DEMO_EMAILS = [self::DEMO_OWNER_EMAIL, self::DEMO_MEMBER_EMAIL];

    public const DEMO_COUPON_CODES = ['WELCOME10', 'EXPIRED20'];

    public const DEMO_TICKET_NUMBERS = ['DEMO-1', 'DEMO-2'];

    public function seed(bool $withDemo): void
    {
        app(DefaultSettingsSeeder::class)->run();
        app(PlansAndFeaturesSeeder::class)->run();

        if ($withDemo && ! User::where('email', self::DEMO_OWNER_EMAIL)->exists()) {
            $this->seedDemoDataset();
        }
    }

    private function seedDemoDataset(): void
    {
        DB::transaction(function (): void {
            $owner = User::create([
                'name' => 'Dana Demo',
                'email' => self::DEMO_OWNER_EMAIL,
                'password' => 'password',
                'email_verified_at' => now(),
            ]);

            // — Acme Inc: active Pro subscription on Stripe —
            $acme = Workspace::create([
                'owner_id' => $owner->id,
                'name' => 'Acme Inc',
                'slug' => 'acme-inc',
                'is_personal' => false,
            ]);
            $acme->members()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()]);

            $member = User::create([
                'name' => 'Miles Member',
                'email' => self::DEMO_MEMBER_EMAIL,
                'password' => 'password',
                'email_verified_at' => now(),
            ]);
            $acme->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

            $pro = Plan::where('slug', 'pro')->firstOrFail();
            $stripePrice = $pro->prices()->where('gateway', 'stripe')->where('interval', 'month')->firstOrFail();

            $subscription = SubscriptionFactory::new()
                ->for($acme)
                ->for($pro, 'plan')
                ->create(['gateway' => $stripePrice->gateway]);

            $payments = PaymentFactory::new()
                ->for($acme)
                ->for($subscription)
                ->count(3)
                ->sequence(
                    ['gateway_payment_id' => 'demo_pay_1', 'created_at' => now()->subMonths(2)],
                    ['gateway_payment_id' => 'demo_pay_2', 'created_at' => now()->subMonth()],
                    ['gateway_payment_id' => 'demo_pay_3', 'created_at' => now()->subDay()],
                )
                ->create();

            InvoiceFactory::new()
                ->for($acme)
                ->for($subscription)
                ->numbered(1, 2)
                ->count(2)
                ->sequence(
                    ['payment_id' => $payments[0]->id, 'issued_at' => now()->subMonths(2)],
                    ['payment_id' => $payments[1]->id, 'issued_at' => now()->subMonth(),
                    ],
                )
                ->create();

            // — One workspace per remaining status so admin filters show color —
            $trialOwner = User::create([
                'name' => 'Percy Paddle', 'email' => 'trial@demo.com', 'password' => 'password', 'email_verified_at' => now(),
            ]);
            $trialWorkspace = WorkspaceFactory::new()->for($trialOwner, 'owner')->create(['name' => 'Paddle Trial Co', 'slug' => 'paddle-trial']);
            $trialWorkspace->members()->attach($trialOwner->id, ['role' => 'owner', 'joined_at' => now()]);
            $paddlePrice = $pro->prices()->where('gateway', 'paddle')->where('interval', 'month')->first();
            SubscriptionFactory::new()->for($trialWorkspace)->trialing()
                ->for($pro, 'plan')
                ->create(['gateway' => $paddlePrice ? $paddlePrice->gateway : 'paddle']);

            $pastDueOwner = User::create([
                'name' => 'Sadia SSL', 'email' => 'pastdue@demo.com', 'password' => 'password', 'email_verified_at' => now(),
            ]);
            $pastDueWorkspace = WorkspaceFactory::new()->for($pastDueOwner, 'owner')->create(['name' => 'BD Traders', 'slug' => 'bd-traders']);
            $pastDueWorkspace->members()->attach($pastDueOwner->id, ['role' => 'owner', 'joined_at' => now()]);
            $sslPrice = $pro->prices()->where('gateway', 'sslcommerz')->where('interval', 'month')->first();
            SubscriptionFactory::new()->for($pastDueWorkspace)->pastDue()
                ->for($pro, 'plan')
                ->create(['gateway' => $sslPrice ? $sslPrice->gateway : 'sslcommerz']);

            $canceledOwner = User::create([
                'name' => 'Chris Cancel', 'email' => 'canceled@demo.com', 'password' => 'password', 'email_verified_at' => now(),
            ]);
            $canceledWorkspace = WorkspaceFactory::new()->for($canceledOwner, 'owner')->create(['name' => 'Gone Ltd', 'slug' => 'gone-ltd']);
            $canceledWorkspace->members()->attach($canceledOwner->id, ['role' => 'owner', 'joined_at' => now()]);
            SubscriptionFactory::new()->for($canceledWorkspace)->canceled()
                ->for($pro, 'plan')
                ->create(['gateway' => $stripePrice->gateway]);

            // — Coupons: one healthy, one expired —
            Coupon::firstOrCreate(['code' => 'WELCOME10'], [
                'type' => 'percent', 'value' => 10, 'currency' => null, 'plan_ids' => null,
                'max_redemptions' => null, 'redeemed_count' => 0, 'expires_at' => now()->addMonth(), 'is_active' => true,
            ]);
            Coupon::firstOrCreate(['code' => 'EXPIRED20'], [
                'type' => 'percent', 'value' => 20, 'currency' => null, 'plan_ids' => null,
                'max_redemptions' => null, 'redeemed_count' => 5, 'expires_at' => now()->subWeek(), 'is_active' => true,
            ]);

            // — Support tickets with a conversation —
            $ticketOne = SupportTicketFactory::new()->for($owner)->for($acme)
                ->create(['number' => 'DEMO-1', 'subject' => 'Invoice PDF shows wrong billing address']);
            TicketMessage::create(['support_ticket_id' => $ticketOne->id, 'user_id' => $owner->id, 'is_staff' => false, 'body' => 'Hi, my last invoice lists the old address.']);
            TicketMessage::create(['support_ticket_id' => $ticketOne->id, 'user_id' => $owner->id, 'is_staff' => true, 'body' => 'Thanks for the report — updated the invoice template, regenerated the PDF.']);

            $ticketTwo = SupportTicketFactory::new()->for($owner)->for($acme)
                ->create(['number' => 'DEMO-2', 'subject' => 'Can I pay via SSLCommerz in USD?', 'status' => 'open']);
            TicketMessage::create(['support_ticket_id' => $ticketTwo->id, 'user_id' => $owner->id, 'is_staff' => false, 'body' => 'Our team is based in Dhaka — is BDT the only option?']);
        });
    }
}
