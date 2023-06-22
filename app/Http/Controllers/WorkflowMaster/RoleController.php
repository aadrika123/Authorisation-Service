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
     * | Create Role
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
            $create = new WfRole();
            $create->addRole($req);

            return responseMsg(true, "Successfully Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //update master
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
            $update = new WfRole();
            $list  = $update->updateRole($req);

            return responseMsg(true, "Successfully Updated", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //master list by id
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
            $listById = new WfRole();
            $list  = $listById->rolebyId($req);

            if ($list->isEmpty())
                throw new Exception("No data Found");

            return responseMsg(true, "Role List", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //all master list
    public function getAllRoles()
    {
        try {
            $list = new WfRole();
            $masters = $list->roleList();

            if ($masters->isEmpty())
                throw new Exception("No data Found");

            return responseMsg(true, "All Role List", $masters);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //delete master
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
            $delete = new WfRole();
            $delete->deleteRole($req);

            return responseMsg(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
