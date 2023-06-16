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
            return response()->json(false, $e->getMessage());
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
             return response()->json(false, $e->getMessage());
         }
     }

     //master list by id
    public function roleMapbyId(Request $req)
    {
        try {

            $listById = new WfWorkflowrolemap();
            $list  = $listById->listbyId($req);

            return responseMsg(true, "Role Map List", $list);
        } catch (Exception $e) {
            return response()->json(false, $e->getMessage());
        }
    }

    //all master list
    public function getAllRoleMap()
    {
        try {

            $list = WfWorkflowrolemap::paginate(10);
            $masters = $list->roleMaps();

            return responseMsg(true, "All Role Map List", $masters);
        } catch (Exception $e) {
            return response()->json(false, $e->getMessage());
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
            return response()->json($e, 400);
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
            // $wfMasterId = $req->workflowId;                  // wfMasterId from frontend in the key of wokflowId
            $workflowId = $req->workflowId;                  // wfMasterId from frontend in the key of wokflowId
            // $ulbWorkflow = $mWfWorkflows->getulbWorkflowId($wfMasterId, $ulbId);
            // $workflowId  =  $ulbWorkflow->id;

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
