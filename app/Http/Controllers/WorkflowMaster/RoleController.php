<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Models\Workflows\WfRole;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{

    /**
     * | Create Workflow Role
     */
    public function createRole(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            ['roleName' => 'required']
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $mWfRole = new WfRole();
            $mWfRole->addRole($req);

            return responseMsg(true, "Successfully Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Workflow Role
     */
    public function editRole(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'id' => 'required',
                'roleName' => 'required',
                'isSuspended' => 'required|bool'
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $mWfRole = new WfRole();
            $role    = $mWfRole->updateRole($req);

            return responseMsg(true, "Successfully Updated", $role);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Get Role by Id
     */
    public function getRole(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            ['id' => 'required|numeric']
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $mWfRole = new WfRole();
            $list  = $mWfRole->rolebyId($req);

            if ($list->isEmpty())
                throw new Exception("No data Found");

            return responseMsg(true, "Role List", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Role List
     */
    public function getAllRoles()
    {
        try {
            $mWfRole = new WfRole();
            $roles = $mWfRole->roleList();

            if ($roles->isEmpty())
                throw new Exception("No data Found");

            return responseMsg(true, "All Role List", $roles);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete Role
     */
    public function deleteRole(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            ['id' => 'required|numeric']
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $mWfRole = new WfRole();
            $mWfRole->deleteRole($req);

            return responseMsg(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
