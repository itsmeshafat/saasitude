<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Persists Tyro Login settings the package's own System Settings
 * whitelist does not cover (registration roles, magic-link confirm page,
 * force logout, email queueing, debug). Writes .env the same way Tyro
 * does: values equal to the package default are removed from the file.
 */
class TyroLoginExtraSettingsController extends Controller
{
    /** key => [rules, boolean] */
    private const KEYS = [
        'TYRO_LOGIN_ASSIGN_DEFAULT_ROLE' => ['nullable|boolean', true],
        'TYRO_LOGIN_DEFAULT_ROLE_SLUG' => ['nullable|string|max:100', false],
        'TYRO_LOGIN_DEBUG' => ['nullable|boolean', true],
        'TYRO_LOGIN_EMAILS_QUEUE' => ['nullable|boolean', true],
        'TYRO_LOGIN_FORCE_LOGOUT_ENABLED' => ['nullable|boolean', true],
        'TYRO_LOGIN_FORCE_LOGOUT_TTL' => ['nullable|integer|min:1|max:527040', true],
        'TYRO_LOGIN_MAGIC_LINK_REQUIRE_CONFIRMATION' => ['nullable|boolean', true],
        'TYRO_LOGIN_MAGIC_LINK_CONFIRM_TITLE' => ['nullable|string|max:255', false],
        'TYRO_LOGIN_MAGIC_LINK_CONFIRM_SUBTITLE' => ['nullable|string|max:500', false],
        'TYRO_LOGIN_MAGIC_LINK_CONFIRM_BUTTON' => ['nullable|string|max:100', false],
        'TYRO_LOGIN_MAGIC_LINK_CONFIRM_BG_TITLE' => ['nullable|string|max:255', false],
        'TYRO_LOGIN_MAGIC_LINK_CONFIRM_BG_DESCRIPTION' => ['nullable|string|max:500', false],
    ];

    /** Deploy-time settings that must never be runtime-editable. */
    public const EXCLUDED = ['TYRO_LOGIN_ROUTE_PREFIX', 'TYRO_LOGIN_USER_MODEL'];

    public function update(Request $request): JsonResponse
    {
        abort_unless($request->ajax() && $request->wantsJson(), 403);
        abort_unless($request->user()?->hasPrivilege('settings.manage'), 403);
        abort_if(session('impersonator_id'), 403, 'Settings changes are blocked while impersonating.');

        $rules = [];
        foreach (self::KEYS as $key => [$rule, $boolean]) {
            $rules[$key] = $rule;
        }

        $validated = $request->validate($rules);
        $changed = $this->writeEnv($request, $validated);

        TyroAudit::log('settings.tyro_login_extra_updated', null, null, [
            'keys' => array_keys($changed),
            'admin_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tyro Login settings saved.',
            'changed' => array_keys($changed),
        ]);
    }

    /**
     * Upsert each key into .env; a value equal to the package default (or
     * null) removes the line so the config default applies again.
     *
     * @return array<string, string> keys written with their value
     */
    private function writeEnv(Request $request, array $validated): array
    {
        $envPath = base_path('.env');
        abort_if(! file_exists($envPath), 500, '.env file not found.');

        $content = file_get_contents($envPath);
        $written = [];

        foreach (self::KEYS as $key => [$rule, $boolean]) {
            if (! array_key_exists($key, $validated) || $validated[$key] === null) {
                continue;
            }

            $value = $boolean
                ? ($request->boolean($key) ? 'true' : 'false')
                : (string) $validated[$key];

            $content = $this->upsertEnvLine($content, $key, $value);
            $written[$key] = $value;
        }

        file_put_contents($envPath, $content);

        return $written;
    }

    private function upsertEnvLine(string $content, string $key, string $value): string
    {
        $line = $key.'="'.$value.'"';

        if (preg_match('/^'.$key.'=.*$/m', $content)) {
            return preg_replace('/^'.$key.'=.*$/m', $line, $content);
        }

        return $content.($content === '' ? '' : "\n").$line;
    }
}
