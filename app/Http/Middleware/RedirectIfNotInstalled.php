<?php

namespace App\Http\Middleware;

use App\Services\Install\EnvWriter;
use App\Services\Install\InstallerState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Global pre-install gate: while the installed.lock marker is absent,
 * every request except the installer itself (and health/up) redirects
 * to /install so the wizard is the only usable surface.
 *
 * Also bootstraps the runtime APP_KEY on the very first request: a
 * fresh clone has no .env at all, and the web group's cookie
 * encryption needs a key before even step 1 can render.
 */
class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! InstallerState::installed() && empty(config('app.key'))) {
            $writer = app(EnvWriter::class);
            $key = $writer->ensureAppKey();

            // .env.example defaults sessions/cache to the database, but no
            // usable DB exists before step 2 — pin file stores so the
            // wizard renders. DatabaseTester restores the DB stores after
            // migrations run.
            $writer->write(['SESSION_DRIVER' => 'file', 'CACHE_STORE' => 'file']);
            config(['app.key' => $key, 'session.driver' => 'file', 'cache.default' => 'file']);
        }

        if (InstallerState::installed()) {
            return $next($request);
        }

        if ($request->is('install') || $request->is('install/*') || $request->is('up')) {
            return $next($request);
        }

        // Assets must keep serving so the wizard itself is styled.
        if ($request->is('build/*') || $request->is('assets/*') || str_ends_with($request->path(), '.css') || str_ends_with($request->path(), '.js') || str_ends_with($request->path(), '.svg')) {
            return $next($request);
        }

        return redirect('/install');
    }
}
