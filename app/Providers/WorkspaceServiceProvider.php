<?php

namespace App\Providers;

use App\Actions\CreateWorkspace;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class WorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(Registered::class, function (Registered $event): void {
            app(CreateWorkspace::class)->execute($event->user, $event->user->name, true);
        });
    }
}
