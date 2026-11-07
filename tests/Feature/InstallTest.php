<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Install\EnvWriter;
use App\Services\Install\InstallerState;
use App\Services\SettingsService;
use Database\Seeders\StaffRolesSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallTest extends TestCase
{
    use RefreshDatabase;

    private string $lockPath;

    private string $envPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lockPath = InstallerState::lockPath();
        $this->envPath = base_path('.env');
        File::ensureDirectoryExists(dirname($this->lockPath));
        File::delete($this->lockPath);
        // Point the EnvWriter at a scratch file so tests never touch the real .env.
        $this->app->instance(EnvWriter::class, new EnvWriter(
            $this->envPath = sys_get_temp_dir().'/install-test-'.uniqid().'.env',
        ));
    }

    protected function tearDown(): void
    {
        File::delete([$this->lockPath, $this->envPath]);
        parent::tearDown();
    }

    /* ---- access control ---- */

    public function test_requirements_page_renders_when_not_installed(): void
    {
        $this->get('/install')->assertRedirect('/install/requirements');
        $this->get('/install/requirements')->assertOk()->assertSee('Requirements');
    }

    public function test_install_routes_404_after_lock_file_exists(): void
    {
        touch($this->lockPath);
        $this->get('/install/requirements')->assertNotFound();
        $this->post('/install/database')->assertNotFound();
    }

    public function test_pre_install_root_redirects_to_installer(): void
    {
        $this->get('/')->assertRedirect('/install');
    }

    public function test_installed_root_does_not_redirect(): void
    {
        touch($this->lockPath);
        $this->get('/')->assertOk();
    }

    public function test_health_endpoint_stays_available_pre_install(): void
    {
        $this->get('/up')->assertOk();
    }

    /* ---- step 2: database ---- */

    public function test_database_step_with_invalid_credentials_shows_error(): void
    {
        $this->post('/install/database', [
            'connection' => 'sqlite',
            'database' => '/nonexistent-'.uniqid().'/db.sqlite',
        ])->assertSessionHasErrors('database');
    }

    public function test_database_step_with_valid_sqlite_writes_env_and_advances(): void
    {
        $db = sys_get_temp_dir().'/install-db-'.uniqid().'.sqlite';
        touch($db);

        $this->post('/install/database', [
            'connection' => 'sqlite',
            'database' => $db,
        ])->assertRedirect('/install/admin');

        $this->assertFileExists($this->envPath);
        $env = file_get_contents($this->envPath);
        $this->assertMatchesRegularExpression('/^DB_CONNECTION="sqlite"$/m', $env);
        $this->assertMatchesRegularExpression('/^APP_KEY="base64:.+"$/m', $env);
        File::delete($db);
    }

    /* ---- step 3: admin ---- */

    public function test_admin_step_creates_super_admin_with_workspace(): void
    {
        $this->post('/install/admin', [
            'name' => 'Site Owner',
            'email' => 'owner@install.test',
            'password' => 'SuperSecret123',
            'password_confirmation' => 'SuperSecret123',
        ])->assertRedirect('/install/gateway');

        $user = User::where('email', 'owner@install.test')->firstOrFail();
        $this->assertTrue($user->roles()->where('slug', 'super-admin')->exists());
        $this->assertSame(1, Workspace::where('owner_id', $user->id)->count());
    }

    public function test_admin_step_rejects_mismatched_password(): void
    {
        $this->post('/install/admin', [
            'name' => 'Site Owner',
            'email' => 'owner@install.test',
            'password' => 'SuperSecret123',
            'password_confirmation' => 'Different123',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'owner@install.test']);
    }

    /* ---- step 4: gateway (optional) ---- */

    public function test_gateway_step_can_be_skipped(): void
    {
        $this->post('/install/gateway', ['action' => 'skip'])
            ->assertRedirect('/install/demo');
    }

    public function test_gateway_step_stores_encrypted_secrets(): void
    {
        $this->post('/install/gateway', [
            'action' => 'save',
            'stripe_mode' => 'test',
            'stripe_secret' => 'sk_test_install',
        ])->assertRedirect('/install/demo');

        $row = Setting::where('key', 'gateway.stripe.secret_key')->firstOrFail();
        $this->assertTrue((bool) $row->is_secret);
        $this->assertNotSame('sk_test_install', $row->value);
        $this->assertSame('sk_test_install', app(SettingsService::class)->get('gateway.stripe.secret_key'));
    }

    /* ---- step 5: finish ---- */

    public function test_finish_without_demo_locks_installer(): void
    {
        foreach (['admin', 'super-admin', 'user'] as $slug) {
            Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);
        }
        $this->seed(StaffRolesSeeder::class);

        $this->post('/install/demo', ['demo' => '0'])
            ->assertRedirect('/login');

        $this->assertFileExists($this->lockPath);
        $this->get('/install/requirements')->assertNotFound();
    }
}
