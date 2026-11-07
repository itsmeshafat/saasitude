<?php

namespace App\Http\Middleware;

use App\Services\Install\InstallerState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Installer route guard: once installed.lock exists, /install* is gone (404). */
class BlockIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(InstallerState::installed(), 404);

        return $next($request);
    }
}
