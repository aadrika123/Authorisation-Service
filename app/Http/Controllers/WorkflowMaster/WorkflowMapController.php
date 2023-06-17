<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Repository\WorkflowMaster\Interface\iWorkflowMapRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowMapController extends Controller
{
    protected $wfMap;
    // Initializing Construct function
    public function __construct(iWorkflowMapRepository $wfMap)
    {
        $this->wfMap = $wfMap;
    }

    //Mapping 
    public function getRoleDetails(Request $request)
    {   
        
        $ulbId = auth()->user()->ulb_id;
        $request->validate([
            'workflowId' => 'required|int',
            'wfRoleId' => 'required|int'

        ]);

        $roledetails = new WfWorkflowrolemap();
        $get = $roledetails->getRoleDetails($request);
        return responseMsg(true, 'All Role Deatils' , $get);
    }    



    public function getUserById(Request $request)
    {
        return $this->wfMap->getUserById($request);
    }

    public function getWorkflowNameByUlb(Request $request)
    {
        return $this->wfMap->getWorkflowNameByUlb($request);
    }

    public function getRoleByUlb(Request $request)
    {
        return $this->wfMap->getRoleByUlb($request);
    }

    public function getWardByUlb(Request $request)
    {
        return $this->wfMap->getWardByUlb($request);
    }

    public function getUserByRole(Request $request)
    {
        return $this->wfMap->getUserByRole($request);
    }

    //by model
    public function getRoleByWorkflow(Request $request)
    {
        $ulbId = authUser()->ulb_id;
        $request->validate([
            'workflowId' => 'required|int'
        ]);

        $roledetails = new WfWorkflowrolemap();
        $get = $roledetails->getRoleByWorkflow($request, $ulbId);
        return responseMsg(true, 'All Role Deatils' , $get);
       
    }

    



}
