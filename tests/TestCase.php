<?php

namespace Tests;

use App\Services\Install\InstallerState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Most tests assume an installed app: the pre-install gate would
        // otherwise redirect everything to /install. InstallTest removes
        // the lock again in its own setUp to exercise the wizard.
        InstallerState::lock();
    }

    protected function tearDown(): void
    {
        File::delete(InstallerState::lockPath());
        parent::tearDown();
    }
}
