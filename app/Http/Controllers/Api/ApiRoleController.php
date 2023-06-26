<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiRole;
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
            $mApiRole->store($request);
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
            $mApiRole = ApiRole::find($request->id);

            return responseMsgs(true, "Api Role!", $mApiRole, "", "01", "", "POST", "");
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
            $mApiRole = ApiRole::all();

            return responseMsgs(true, "List of Api Role!", $mApiRole, "", "01", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", "", "POST", "");
        }
    }
}
