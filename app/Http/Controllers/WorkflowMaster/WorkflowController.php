<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Models\Workflows\WfWorkflow;
use Illuminate\Http\Request;
use Exception;

class WorkflowController extends Controller
{
    //create master
    public function createWorkflow(Request $req)
    {
        try {
            $req->validate([
                'wfMasterId' => 'required',
                'ulbId' => 'required',
                'altName' => 'required',
                'isDocRequired' => 'required',
            ]);

            $create = new WfWorkflow();
            $create->addWorkflow($req);

            return responseMsg(true, "Workflow Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //update master
    public function updateWorkflow(Request $req)
    {
        try {
            $req->validate([
                'wfMasterId' => 'required',
                'ulbId' => 'required',
            ]);
            $update = new WfWorkflow();
            $list  = $update->updateWorkflow($req);

            return responseMsg(true, "Successfully Updated", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //master list by id
    public function workflowbyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $listById = new WfWorkflow();
            $list  = $listById->listbyId($req);

            return responseMsg(true, "Workflow List", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //all master list
    public function getAllWorkflow(Request $req)
    {
        try {
            $ulbId = authUser()->ulb_id;
            $list = new WfWorkflow();
            $workflow = $list->listUlbWorkflow($ulbId);

            return responseMsg(true, "All Workflow List", $workflow);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    //delete master
    public function deleteWorkflow(Request $req)
    {
        try {
            $delete = new WfWorkflow();
            $delete->deleteWorkflow($req);

            return responseMsg(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
