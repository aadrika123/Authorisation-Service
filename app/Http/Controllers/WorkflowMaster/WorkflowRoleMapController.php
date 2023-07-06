<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Models\Workflows\WfWorkflow;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Traits\Workflow\Workflow;
use Exception;
use Illuminate\Http\Request;


class WorkflowRoleMapController extends Controller
{

    /**
     * Created On-13-06-2022 
     * Created By-Tannu Verma
     */

    use Workflow;

    public function createRoleMap(Request $req)
    {
        try {
            $req->validate([
                'workflowId' => 'required',
                'wfRoleId' => 'required',
                // 'forwardRoleId' => 'required',
                // 'backwardRoleId' => 'required',
            ]);

            $create = new WfWorkflowrolemap();
            $create->addRoleMap($req);

            return responseMsg(true, "Successfully Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //update master
    public function updateRoleMap(Request $req)
    {
        try {
            $update = new WfWorkflowrolemap();
            $list  = $update->updateRoleMap($req);

            return responseMsg(true, "Successfully Updated", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //master list by id
    public function roleMapbyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);

            $listById = new WfWorkflowrolemap();
            $list  = $listById->roleMaps($req)
                ->where('wf_workflowrolemaps.id', $req->id)
                ->first();

            return responseMsg(true, "Role Map List", remove_null($list));
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    /**
     * | All Role Maps
     */
    public function getAllRoleMap()
    {
        try {

            $list = new WfWorkflowrolemap();
            $masters = $list->roleMaps()->get();

            return responseMsg(true, "All Role Map List", $masters);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //delete master
    public function deleteRoleMap(Request $req)
    {
        try {
            $delete = new WfWorkflowrolemap();
            $delete->deleteRoleMap($req);

            return responseMsg(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //Workflow Info
    public function workflowInfo(Request $req)
    {
        try {
            //workflow members
            $mWorkflowMap = new WfWorkflow();
            $mWfWorkflows = new WfWorkflow();
            $mWfWorkflowrolemap = new WfWorkflowrolemap();
            $ulbId = authUser()->ulb_id;
            $workflowId = $req->workflowId;

            $mreqs = new Request(["workflowId" => $workflowId]);
            $role = $mWorkflowMap->getRoleByWorkflow($mreqs);

            $data['members'] = collect($role)['original']['data'];

            //logged in user role
            $role = $this->getRole($mreqs);
            if ($role->isEmpty())
                throw new Exception("You are not authorised");
            $roleId  = collect($role)['wf_role_id'];

            //members permission
            $data['permissions'] = $this->permission($workflowId, $roleId);

            // pseudo users
            $data['pseudoUsers'] = $this->pseudoUser($ulbId);

            return responseMsgs(true, "Workflow Information", remove_null($data));
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
