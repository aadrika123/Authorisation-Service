<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Models\Workflows\WfRoleusermap;
// use App\Repository\WorkflowMaster\Interface\iWorkflowRoleUserMapRepository;
use Carbon\Carbon;
use Dotenv\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class WorkflowRoleUserMapController extends Controller
{
    protected $eloquentRoleUserMap;

    // Initializing Construct function
    // public function __construct(iWorkflowRoleUserMapRepository $eloquentRoleUserMap)
    // {
    //     $this->EloquentRoleUserMap = $eloquentRoleUserMap;
    // }


    /**
     * | Create ROle User Mapping 
     */
    public function createRoleUser(Request $request)
    {
        $createdBy = Auth()->user()->id;

        try {
            $checkExisting = WfRoleusermap::where('wf_role_id', $request->wfRoleId)
                ->where('user_id', $request->userId)
                ->first();

            if ($checkExisting)
                throw new Exception('This Role is already assigned to this user');

            // create
            $device = new WfRoleusermap;
            $device->wf_role_id = $request->wfRoleId;
            $device->user_id = $request->userId;
            $device->created_by = $createdBy;
            $device->save();
            return responseMsg(true, "Successfully Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * Update data
     */
    public function updateRoleUser(Request $request)
    {
        try {
            $device = WfRoleusermap::find($request->id);
            $device->wf_role_id = $request->wfRoleId ?? $device->wf_role_id;
            $device->user_id = $request->userId ?? $device->user_id;
            $device->save();

            return responseMsg(true, "Data Updated", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * Delete data
     */
    public function deleteRoleUser(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $data = WfRoleusermap::find($request->id);
            $data->is_suspended = true;
            $data->save();

            return responseMsg(true, "Data Deleted", $data);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }



    /**
     * Get All data
     */
    public function getAllRoleUser(Request $request)
    {
        try {
            $ulbId = authUser()->ulb_id;
            $mWfRoleusermap = new WfRoleusermap();
            $data = $mWfRoleusermap->getRoleUser()
                ->where('users.ulb_id', $ulbId)
                ->get();

            return responseMsg(true, "Successfully Saved", $data);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * list view by IDs
     */

    public function roleUserbyId(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            $mWfRoleusermap = new WfRoleusermap();
            $data = $mWfRoleusermap->getRoleUser()
                ->where('wf_roleusermaps.id', $request->id)
                ->first();

            return responseMsg(true, "Data Retrieved", $data);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    // Get Permitted Roles By User ID
    public function getRolesByUserId(Request $req)
    {
        $validated = FacadesValidator::make(
            $req->all(),
            [
                'userId' => 'required'
            ]
        );
        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validated->errors()
            ], 422);
        }

        return $this->EloquentRoleUserMap->getRolesByUserId($req);
    }

    // Enable or Disable User Roles
    public function updateUserRoles(Request $req)
    {
        $validated = FacadesValidator::make(
            $req->all(),
            [
                'roleId' => 'required|int',
                'is_suspended' => 'required|bool',
                'userId' => 'required|int'
            ]
        );
        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validated->errors()
            ], 422);
        }
        return $this->EloquentRoleUserMap->updateUserRoles($req);
    }
}
