<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttachUserRole
{
    public function handle(Request $request, Closure $next)
    {
        if ($user = auth()->user()) {
            $roleData = DB::table('users')
                ->select('wf_roles.id as wfRoleId', 'wf_roles.role_name')
                ->leftJoin('wf_roleusermaps', 'wf_roleusermaps.user_id', 'users.id')
                ->leftJoin('wf_roles', 'wf_roles.id', 'wf_roleusermaps.wf_role_id')
                ->where('users.id', $user->id)
                ->where('wf_roleusermaps.is_suspended', false)
                ->first();

            if ($roleData) {
                $user->wfRoleId = $roleData->wfRoleId;
                $user->role_name = $roleData->role_name;
            }
        }

        return $next($request);
    }
}
