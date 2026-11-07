<?php

namespace App\Http\Middleware;

use App\Services\Install\InstallerState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Global pre-install gate: while the installed.lock marker is absent,
 * every request except the installer itself (and health/up) redirects
 * to /install so the wizard is the only usable surface.
 */
class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
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
