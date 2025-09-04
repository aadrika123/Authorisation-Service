<?php

namespace App\Http\Middleware;

use App\Models\Api\ApiRegistry as ApiApiRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use App\Models\RoleApiMap;
use App\Models\ApiRegistry;
use App\Models\Workflows\WfRoleusermap;

class RoleApiPermissionMiddleware
{
    private $_user;
    private $_roleApiCache;

    public function handle(Request $request, Closure $next)
    {
     
        $this->_user = Auth::user();

        if (!$this->_user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Load cached Role-API map
        $this->loadRoleApiData();

        // Fetch user roles
        $roles = (new WfRoleusermap())
            ->getRoleDetailsByUserId($this->_user->id)
            ->pluck("roleId")
            ->unique();
            dd($roles);

        if ($roles->isEmpty()) {
            return response()->json(['message' => 'Forbidden: No roles assigned'], 403);
        }

        // Normalize endpoint + method
        $url = trim($request->path(), "/");
        $method = strtoupper($request->getMethod());

        // Find matching API from registry
        $api = ApiApiRegistry::where("end_point", $url)
            ->where("method", $method)
            ->first();

        if (!$api) {
            return response()->json(['message' => 'Forbidden: API not registered'], 403);
        }

        // Verify mapping
        $hasAccess = RoleApiMap::whereIn("role_id", $roles->toArray())
            ->where("api_mstr_id", $api->id)
            ->where("status", 1)
            ->where("is_suspended", false)
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Forbidden: Access denied'], 403);
        }

        return $next($request);
    }

    /**
     * Load Role-API mappings into cache (Redis).
     */
    private function loadRoleApiData()
    {
        $this->_roleApiCache = json_decode(Redis::get("ROLE_API_MAP"), true);

        if (!$this->_roleApiCache) {
            Redis::del("ROLE_API_MAP");

            $roleApiMaps = RoleApiMap::select("role_api_maps.*", "api_registries.end_point", "api_registries.method")
                ->join("api_registries", "api_registries.id", "role_api_maps.api_mstr_id")
                ->where("role_api_maps.status", 1)
                ->get()
                ->toArray();

            Redis::set("ROLE_API_MAP", json_encode($roleApiMaps));
            $this->_roleApiCache = $roleApiMaps;
        }

        $this->_roleApiCache = collect($this->_roleApiCache);
    }
}
