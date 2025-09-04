<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiRolemap;
use App\Models\RoleApiMap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\FlareClient\Api;

class ApiRoleMapController extends Controller
{
    /**
     * |  Create Api Role Mapping
     */
    public function createRoleMap(Request $req)
    {
        try {
            $req->validate([
                'apiId'     => 'required',
                'apiRoleId' => 'required',
                'isSuspended' => 'nullable|boolean'
            ]);
            $mApiRolemap = new ApiRolemap();
            $checkExisting = $mApiRolemap->where('api_id', $req->apiId)
                ->where('api_role_id', $req->apiRoleId)
                ->first();

            if ($checkExisting) {
                $req->merge([
                    'id' => $checkExisting->id,
                    'isSuspended' => $req->isSuspended
                ]);
                $mApiRolemap->updateRoleMap($req);
            } else {
                $mApiRolemap->addRoleMap($req);
            }


            // if ($checkExisting)
            //     throw new Exception('Api Already Maps to Api Role');
            // $mreqs = [
            //     'apiId'     => $req->apiId,
            //     'apiRoleId' => $req->apiRoleId
            // ];
            // $mApiRolemap->addRoleMap($mreqs);

            return responseMsg(true, "Data Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Api Role Mapping
     */
    public function updateRoleMap(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $mApiRolemap = new ApiRolemap();
            $list  = $mApiRolemap->updateRoleMap($req);

            return responseMsg(true, "Successfully Updated", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Api Role Mapping By id
     */
    public function roleMapbyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);

            $mApiRolemap = new ApiRolemap();
            $list  = $mApiRolemap->roleMaps($req)
                ->where('api_rolemaps.id', $req->id)
                ->where('api_rolemaps.is_suspended', false)
                ->first();

            return responseMsg(true, "Api Role Map", remove_null($list));
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    /**
     * | Api Role Mapping List
     */
    public function getAllRoleMap()
    {
        try {
            $mApiRolemap = new ApiRolemap();
            $menuRole = $mApiRolemap->roleMaps()
                ->where('api_rolemaps.is_suspended', false)
                ->get();

            return responseMsg(true, "Api Role Map List", $menuRole);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete Api Role Mapping
     */
    public function deleteRoleMap(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $delete = new ApiRolemap();
            $delete->deleteRoleMap($req);

            return responseMsg(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
    /**
     * |
     */
    public function createApiRoleMap(Request $req)
    {
        try {
            $req->validate([
                'roleId'      => 'required',
                'apiId'       => 'required',
                'isSuspended' => 'nullable|boolean'

            ]);
            $mapiRolemap = new RoleApiMap();
            $checkExisting = $mapiRolemap->where('api_mstr_id', $req->apiId)
                ->where('role_id', $req->roleId)
                ->first();

            if ($checkExisting) {
                $req->merge([
                    'id' => $checkExisting->id,
                    'isSuspended' => $req->isSuspended
                ]);
                $mapiRolemap->updateRoleMap($req);
            } else {
                $mapiRolemap->addRoleMap($req);
            }

            // if ($checkExisting)
            //     throw new Exception('Menu Already Maps to Menu Role');
            // $mreqs = [
            //     'menuId'     => $req->menuId,
            //     'menuRoleId' => $req->menuRoleId
            // ];
            // $mMenuRolemap->addRoleMap($mreqs);

            return responseMsg(true, "Data Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    public function apiRoleList(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'roleId' => 'required|int',
                'moduleId'   => 'required|int',
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $user = authUser();

            $query = "
            SELECT 
                ar.id,
                ar.module_id,
                ar.end_point,
                CASE 
                    WHEN rpm.api_mstr_id IS NULL THEN false
                    ELSE true
                END AS permission_status
            FROM api_registries AS ar
            LEFT JOIN (
                SELECT * 
                FROM role_api_maps 
                WHERE role_id = :roleId 
                  AND is_suspended = false
            ) AS rpm 
              ON rpm.api_mstr_id = ar.id
            WHERE ar.module_id = :moduleId
            ORDER BY rpm.id NULLS LAST, ar.id ASC
        ";

            $data = DB::select($query, [
                'roleId'   => $req->menuRoleId,
                'moduleId' => $req->moduleId,
            ]);

            return responseMsg(true, "API List of Module", $data);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), []);
        }
    }
}
