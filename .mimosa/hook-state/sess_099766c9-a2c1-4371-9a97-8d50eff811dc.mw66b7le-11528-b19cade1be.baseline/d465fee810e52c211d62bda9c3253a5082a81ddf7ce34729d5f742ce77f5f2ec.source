<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SidebarColors;
use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Saves the LIGHT-mode sidebar palette (Appearance → Sidebar → Light mode).
 * The vendor hub's save only handles a single (dark) palette via .env; the
 * light palette lives in storage/app/sidebar-colors.json, applied under
 * html.light in styles.blade.php.
 */
class SidebarLightColorsController extends Controller
{
    private const KEYS = [
        '--sidebar', '--sidebar-foreground', '--sidebar-primary',
        '--sidebar-primary-foreground', '--sidebar-accent',
        '--sidebar-accent-foreground', '--sidebar-header-border',
    ];

    public function update(Request $request): JsonResponse
    {
        abort_unless($request->ajax() && $request->wantsJson(), 403);
        abort_unless($request->user()?->hasPrivilege('settings.manage'), 403);
        abort_if(session('impersonator_id'), 403, 'Settings changes are blocked while impersonating.');

        $rules = [];
        foreach (self::KEYS as $key) {
            $rules[$key] = 'nullable|regex:/^#[0-9a-fA-F]{3,8}$/';
        }

        $validated = $request->validate($rules);
        $values = array_filter($validated, fn ($v) => $v !== null);

        SidebarColors::saveLight($values);

        TyroAudit::log('settings.sidebar_light_updated', null, null, [
            'keys' => array_keys($values),
            'admin_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Light-mode sidebar palette saved.',
            'changed' => array_keys($values),
        ]);
    }
}
