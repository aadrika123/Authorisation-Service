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
                ->select('wf_roles.id as wfRoleId', 'wf_roles.role_name', 'ulb_masters.ulb_name', 'users.emp_id')
                ->leftJoin('wf_roleusermaps', 'wf_roleusermaps.user_id', 'users.id')
                ->leftJoin('wf_roles', 'wf_roles.id', 'wf_roleusermaps.wf_role_id')
                ->leftJoin('ulb_masters', 'ulb_masters.id', 'users.ulb_id')
                ->where('users.id', $user->id)
                ->where('wf_roleusermaps.is_suspended', false)
                ->first();

            if ($roleData) {
                $user->wfRoleId = $roleData->wfRoleId;
                $user->role_name = $roleData->role_name;
                $user->ulb_name = $roleData->ulb_name;
                $user->emp_id = $roleData->emp_id;
            }

            $userWards = DB::table('wf_ward_users')
                ->select('ward_id')
                ->where('user_id', $user->id)
                ->where('is_suspended', false)
                ->get();

            $user->userWard = $userWards;
        }

        return $next($request);
    }
}
