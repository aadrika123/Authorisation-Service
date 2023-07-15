<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiRoleusermap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiRoleUserMapController extends Controller
{
    /**
     * | Create api Role Mapping
     */
    public function createRoleUser(Request $req)
    {
        try {
            $req->validate([
                'userId'     => 'required',
                'apiRoleId' => 'required'
            ]);
            $mApiRoleusermap = new ApiRoleusermap();
            $checkExisting = $mApiRoleusermap->where('user_id', $req->userId)
                ->where('api_role_id', $req->apiRoleId)
                ->first();
            if ($checkExisting)
                throw new Exception('User Already Maps to this Api Role');

            $mApiRoleusermap->addRoleUser($req);

            return responseMsgs(true, "Data Saved", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update api Role Mapping
     */
    public function updateRoleUser(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $mApiRoleusermap = new ApiRoleusermap();
            $mApiRoleusermap->updateRoleUser($req);

            return responseMsgs(true, "Data Updated", []);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | api Role Mapping By id
     */
    public function roleUserbyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);

            $mApiRoleusermap = new ApiRoleusermap();
            $list  = $mApiRoleusermap->listRoleUser($req)
                ->where('api_roleusermaps.id', $req->id)
                ->first();

            return responseMsgs(true, "Api Role Map", remove_null($list));
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }


    /**
     * | api Role Mapping List
     */
    public function getAllRoleUser()
    {
        try {
            $mApiRoleusermap = new ApiRoleusermap();
            $menuRole = $mApiRoleusermap->listRoleUser()->get();

            return responseMsgs(true, "Api Role Map List", $menuRole);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete api Role Mapping
     */
    public function deleteRoleUser(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $delete = new ApiRoleusermap();
            $delete->deleteRoleUser($req);

            return responseMsgs(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
    public function roleByUserId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'userId' => 'required|integer'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $apiRoleUserMap = new ApiRoleusermap;
            $data = $apiRoleUserMap->getRoleByUserId()
                ->where('api_roleusermaps.user_id', '=',  $req->userId)
                ->get();
            return responseMsgs(true, 'API Role Map By User Id', $data, "", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    public function roleExcludingUserId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'userId' => 'required|integer'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $apiRoleUserMap = new ApiRoleusermap;
            $data = $apiRoleUserMap->getRoleByUserId()
                ->where('api_roleusermaps.user_id', '!=', $req->userId)
                ->get();
            return responseMsgs(true, 'API Role Map Except User Id', $data, "", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
