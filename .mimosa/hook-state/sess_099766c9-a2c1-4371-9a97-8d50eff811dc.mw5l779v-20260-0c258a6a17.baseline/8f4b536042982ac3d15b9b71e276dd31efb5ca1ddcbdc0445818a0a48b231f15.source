<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Models\Workspace;
use Database\Seeders\StaffRolesSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(string $role = 'admin')
    {
        foreach (['admin', 'super-admin', 'finance', 'support'] as $slug) {
            Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);
        }
        $this->seed(StaffRolesSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', $role)->first());

        return $admin;
    }

    public function test_payments_csv_streams_with_headers(): void
    {
        $admin = $this->makeAdmin();
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $owner->id, 'name' => 'Acme', 'slug' => 'acme', 'is_personal' => true,
        ]);
        Payment::create([
            'workspace_id' => $workspace->id, 'gateway' => 'stripe', 'gateway_payment_id' => 'pi_123',
            'amount' => 1200, 'currency' => 'USD', 'status' => 'succeeded',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.exports.csv', 'payments'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('gateway_payment_id', $content);
        $this->assertStringContainsString('pi_123', $content);
        $this->assertStringContainsString('Acme', $content);
    }

    public function test_payments_csv_honors_status_filter(): void
    {
        $admin = $this->makeAdmin();
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $owner->id, 'name' => 'Acme', 'slug' => 'acme', 'is_personal' => true,
        ]);
        Payment::create(['workspace_id' => $workspace->id, 'gateway' => 'stripe', 'gateway_payment_id' => 'pi_ok', 'amount' => 100, 'currency' => 'USD', 'status' => 'succeeded']);
        Payment::create(['workspace_id' => $workspace->id, 'gateway' => 'stripe', 'gateway_payment_id' => 'pi_bad', 'amount' => 100, 'currency' => 'USD', 'status' => 'failed']);

        $content = $this->actingAs($admin)
            ->get(route('admin.exports.csv', ['type' => 'payments', 'status' => 'succeeded']))
            ->streamedContent();

        $this->assertStringContainsString('pi_ok', $content);
        $this->assertStringNotContainsString('pi_bad', $content);
    }

    public function test_all_export_types_work(): void
    {
        $admin = $this->makeAdmin();

        foreach (['invoices', 'subscriptions', 'workspaces'] as $type) {
            $this->actingAs($admin)
                ->get(route('admin.exports.csv', $type))
                ->assertOk();
        }
    }

    public function test_monthly_report_pdf_downloads(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.exports.report', ['month' => now()->format('Y-m')]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_unknown_type_is_404(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.exports.csv', 'secrets'))->assertNotFound();
    }

    public function test_non_admin_cannot_export(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.exports.csv', 'payments'))
            ->assertRedirect();
    }
}
