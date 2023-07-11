<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiMaster;
use App\Models\Api\ApiRole;
use App\Models\Api\ApiRolemap;
use Exception;
use Illuminate\Http\Request;

class ApiRoleController extends Controller
{
    /**
     * | Save Api Role
     */
    public function createApiRole(Request $request)
    {
        try {
            $request->validate([
                'apiRoleName' => 'required',
            ]);
            $mApiRole = new ApiRole();
            $mApiMaster = new ApiMaster();
            $mApiRolemap = new ApiRolemap();
            $apiRole = $mApiRole->store($request);

            $apiRoleId = $apiRole->id;
            $apis = $mApiMaster->listApi();
            // ->get();
            foreach ($apis as $api) {
                $data['apiId']       = $api->id;
                $data['apiRoleId']   = $apiRoleId;
                $data['isSuspended'] = true;

                //API Role Mapping at the time of Role Creation.
                $mApiRolemap->addRoleMap($data);
            }

            return responseMsgs(true, "Data Saved!", "", "", "02", "", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Api Role
     */
    public function updateApiRole(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'apiRoleName' => 'required',
            ]);
            $mApiRole = new ApiRole();
            $mApiRole->edit($request);
            return responseMsgs(true, "Api Role Updated!", "", "", "02", "733", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete Api Role
     */
    public function deleteApiRole(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            ApiRole::where('id', $request->id)
                ->update(['is_suspended' => true]);
            return responseMsgs(true, "Api Role Deleted!", "", "", "02", "733", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Api Role by Id
     */
    public function getApiRole(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|int'
            ]);
            $mApiRole = new  ApiRole();
            $list = $mApiRole->listApiRole()
                ->where('api_roles.id', $request->id)
                ->first();

            return responseMsgs(true, "Api Role!", $list, "", "01", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", "", "POST", "");
        }
    }

    /**
     * | Api Role List
     */
    public function listApiRole(Request $request)
    {
        try {
            $mApiRole = new  ApiRole();
            $list = $mApiRole->listApiRole()
                ->get();

            return responseMsgs(true, "List of Api Role!", $list, "", "01", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", "", "POST", "");
        }
    }

    /**
     * | Menu Role Mapping List By Menu Role Id
     */
    public function apiByApiRole(Request $req)
    {
        try {
            $req->validate([
                'apiRoleId' => 'required'
            ]);
            $mApiRolemap = new ApiRolemap();
            $apiRole = $mApiRolemap->roleMaps()
                ->where('api_rolemaps.api_role_id', $req->apiRoleId)
                ->get();

            return responseMsg(true, "API Role Map List", $apiRole);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
