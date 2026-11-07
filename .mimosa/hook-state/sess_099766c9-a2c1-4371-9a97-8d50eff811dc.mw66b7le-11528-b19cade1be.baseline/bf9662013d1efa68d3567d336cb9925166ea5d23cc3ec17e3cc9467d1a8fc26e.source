<?php

namespace Tests\Feature;

use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_endpoint_rejects_missing_signature(): void
    {
        $response = $this->postJson('/webhooks/stripe', ['type' => 'test'], []);

        $response->assertStatus(400);
    }

    public function test_webhook_endpoint_rejects_invalid_signature(): void
    {
        $response = $this->post('/webhooks/stripe', [], [
            'Stripe-Signature' => 't=12345,v1=invalidsig',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_event_model_marks_processed(): void
    {
        $event = WebhookEvent::create([
            'gateway' => 'stripe',
            'event_id' => 'evt_test_001',
            'payload' => ['type' => 'invoice.paid'],
            'status' => 'pending',
        ]);

        $event->markProcessed();

        $this->assertSame('processed', $event->fresh()->status);
        $this->assertNotNull($event->fresh()->processed_at);
    }

    public function test_webhook_event_model_marks_failed(): void
    {
        $event = WebhookEvent::create([
            'gateway' => 'stripe',
            'event_id' => 'evt_test_002',
            'payload' => ['type' => 'invoice.payment_failed'],
            'status' => 'pending',
        ]);

        $event->markFailed('Stripe error message');

        $this->assertSame('failed', $event->fresh()->status);
        $this->assertSame(1, $event->fresh()->attempts);
        $this->assertSame('Stripe error message', $event->fresh()->error_message);
    }

    public function test_duplicate_event_id_is_idempotent(): void
    {
        WebhookEvent::create([
            'gateway' => 'stripe',
            'event_id' => 'evt_duplicate',
            'payload' => ['type' => 'invoice.paid'],
            'status' => 'processed',
        ]);

        // Attempting to create with same gateway+event_id should not throw, just return existing
        $existing = WebhookEvent::firstOrCreate(
            ['gateway' => 'stripe', 'event_id' => 'evt_duplicate'],
            ['payload' => ['type' => 'invoice.paid'], 'status' => 'pending']
        );

        $this->assertFalse($existing->wasRecentlyCreated);
        $this->assertDatabaseCount('webhook_events', 1);
    }
}
