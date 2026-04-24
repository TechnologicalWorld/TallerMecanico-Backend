<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetBranchTeam
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            if ($user->current_branch_id) {
                setPermissionsTeamId($user->current_branch_id);
                $request->merge(['current_branch_id' => $user->current_branch_id]);
                if (!$request->has('current_branch')) {
                    $request->attributes->add([
                        'current_branch' => $user->currentBranch
                    ]);
                }
            } else {
                setPermissionsTeamId(null);
            }
        }

        return $next($request);
    }
}