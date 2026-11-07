<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Services\Install\AdminCreator;
use App\Services\Install\DatabaseTester;
use App\Services\Install\DemoDataSeeder;
use App\Services\Install\GatewayConfigurator;
use App\Services\Install\InstallerState;
use App\Services\Install\RequirementsChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PDOException;
use RuntimeException;

/** First-run web installer wizard (thin; logic lives in Services/Install). */
class InstallController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect('/install/requirements');
    }

    /* ---- step 1: requirements ---- */

    public function requirements(RequirementsChecker $checker): View
    {
        return view('install.requirements', [
            'checks' => $checker->check(),
            'passes' => $checker->passes(),
        ]);
    }

    /* ---- step 2: database ---- */

    public function databaseForm(): View
    {
        return view('install.database');
    }

    public function database(Request $request, DatabaseTester $tester): RedirectResponse
    {
        $validated = $request->validate([
            'connection' => 'required|in:mysql,sqlite',
            'host' => 'required_if:connection,mysql|nullable|string|max:255',
            'port' => 'required_if:connection,mysql|nullable|numeric|between:1,65535',
            'database' => 'required|string|max:255',
            'username' => 'required_if:connection,mysql|nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'app_url' => 'nullable|url|max:255',
        ]);

        try {
            $tester->install($validated);
        } catch (PDOException|RuntimeException $e) {
            return back()->withInput($request->except('password'))
                ->withErrors(['database' => 'Could not connect: '.$e->getMessage()]);
        }

        return redirect('/install/admin')->with('install.message', 'Database configured and migrated.');
    }

    /* ---- step 3: admin account ---- */

    public function adminForm(): View
    {
        return view('install.admin');
    }

    public function admin(Request $request, AdminCreator $creator): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $creator->create($validated);

        return redirect('/install/gateway')->with('install.message', 'Administrator account created.');
    }

    /* ---- step 4: gateways (optional) ---- */

    public function gatewayForm(): View
    {
        return view('install.gateway');
    }

    public function gateway(Request $request, GatewayConfigurator $configurator): RedirectResponse
    {
        if ($request->input('action') !== 'save') {
            return redirect('/install/demo');
        }

        $validated = $request->validate([
            'stripe_mode' => 'nullable|in:test,live',
            'stripe_secret' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
            'paddle_mode' => 'nullable|in:test,live',
            'paddle_api_key' => 'nullable|string|max:255',
            'paddle_webhook_secret' => 'nullable|string|max:255',
            'sslcommerz_mode' => 'nullable|in:test,live',
            'sslcommerz_store_id' => 'nullable|string|max:255',
            'sslcommerz_store_password' => 'nullable|string|max:255',
        ]);

        $configurator->save($validated);

        return redirect('/install/demo')->with('install.message', 'Gateway credentials stored (encrypted).');
    }

    /* ---- step 5: demo data + finish ---- */

    public function demoForm(): View
    {
        return view('install.demo');
    }

    public function demo(Request $request, DemoDataSeeder $seeder): RedirectResponse
    {
        $request->validate(['demo' => 'required|boolean']);

        $seeder->seed(withDemo: (bool) $request->boolean('demo'));
        InstallerState::lock();

        return redirect('/login')->with('install.message', 'Installation complete. Welcome aboard!');
    }
}
