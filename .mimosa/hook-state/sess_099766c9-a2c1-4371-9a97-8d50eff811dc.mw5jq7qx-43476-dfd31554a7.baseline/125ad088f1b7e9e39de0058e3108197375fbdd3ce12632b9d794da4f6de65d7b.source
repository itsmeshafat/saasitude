<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates the X-Workspace-Id header against workspace membership and
 * exposes the resolved workspace to controllers via $request->workspace().
 *
 * 404 when the workspace does not exist, 403 when the user is not a member
 * (workspace isolation — never leak existence to non-members via a different
 * status than any other missing id).
 */
class EnsureWorkspaceContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = $request->header('X-Workspace-Id');

        if (! $workspaceId || ! ctype_digit((string) $workspaceId)) {
            return response()->json([
                'message' => 'The X-Workspace-Id header is required.',
                'code' => 'workspace_context_required',
                'errors' => [],
            ], 422);
        }

        /** @var Workspace|null $workspace */
        $workspace = Workspace::query()->find((int) $workspaceId);

        if ($workspace === null) {
            return response()->json([
                'message' => 'Workspace not found.',
                'code' => 'workspace_not_found',
                'errors' => [],
            ], 404);
        }

        $isMember = Cache::remember(
            "workspace_membership:{$workspace->id}:{$request->user()->id}",
            now()->addMinutes(5),
            fn () => $workspace->workspaceMembers()->where('user_id', $request->user()->id)->exists(),
        );

        if (! $isMember) {
            return response()->json([
                'message' => 'You are not a member of this workspace.',
                'code' => 'workspace_forbidden',
                'errors' => [],
            ], 403);
        }

        $request->attributes->set('workspace', $workspace);

        return $next($request);
    }
}
