<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_with_seo(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Launch your SaaS', false)
            ->assertSee('<meta name="description"', false)
            ->assertSee('og:title', false)
            ->assertSee('canonical', false);
    }

    public function test_pricing_shows_db_plans_with_both_currencies(): void
    {
        $plan = Plan::firstOrCreate(['slug' => 'starter'], ['name' => 'Starter', 'description' => 'For teams', 'is_public' => true]);
        $plan->prices()->createMany([
            ['gateway' => 'stripe', 'interval' => 'month', 'currency' => 'USD', 'amount_minor' => 1200],
            ['gateway' => 'sslcommerz', 'interval' => 'month', 'currency' => 'BDT', 'amount_minor' => 99000],
        ]);

        $this->get('/pricing')
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('USD')
            ->assertSee('BDT');
    }

    public function test_archived_plans_hidden_from_pricing(): void
    {
        Plan::create(['name' => 'Secret', 'slug' => 'secret', 'is_public' => false]);

        $this->get('/pricing')->assertOk()->assertDontSee('Secret');
    }

    public function test_contact_form_creates_guest_ticket(): void
    {
        $response = $this->post('/contact', [
            'email' => 'visitor@example.com',
            'subject' => 'Pre-sales question',
            'category' => 'general',
            'body' => 'Do you support annual billing?',
        ]);

        $response->assertRedirect(route('public.contact'));

        $ticket = SupportTicket::query()->where('guest_email', 'visitor@example.com')->first();
        $this->assertNotNull($ticket);
        $this->assertNull($ticket->user_id);
        $this->assertSame('Pre-sales question', $ticket->subject);
    }

    public function test_contact_rate_limited(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $this->post('/contact', [
                'email' => "u{$i}@example.com", 'subject' => 's', 'category' => 'general', 'body' => 'b',
            ]);
        }

        $this->post('/contact', [
            'email' => 'last@example.com', 'subject' => 's', 'category' => 'general', 'body' => 'b',
        ])->assertStatus(429);
    }

    public function test_legal_pages_render_with_review_flag(): void
    {
        foreach (['terms', 'privacy', 'refund-policy'] as $page) {
            $this->get("/{$page}")
                ->assertOk()
                ->assertSee('lawyer review required');
        }

        $this->get('/nonexistent-legal')->assertNotFound();
    }

    public function test_changelog_renders_markdown_entries(): void
    {
        $this->get('/changelog')
            ->assertOk()
            ->assertSee('Changelog');
    }

    public function test_sitemap_lists_public_urls(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<urlset', false)
            ->assertSee(url('/pricing'));
    }
}
