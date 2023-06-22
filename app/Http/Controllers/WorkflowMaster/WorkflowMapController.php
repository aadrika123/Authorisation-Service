<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Models\Workflows\WfRole;
use App\Models\Workflows\WfRoleusermap;
use App\Models\Workflows\WfWorkflow;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Repository\WorkflowMaster\Interface\iWorkflowMapRepository;
use Exception;
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


    //----------------------------------------------------------------------
                 //By Model 
    //----------------------------------------------------------------------
    public function getRoleByWorkflow(Request $request)
    {
        $ulbId = authUser()->ulb_id;
        $request->validate([
            'workflowId' => 'required|int'
        ]);

        $roledetails = new WfWorkflowrolemap();
        $get = $roledetails->getRoleByWorkflow($request, $ulbId);
        return responseMsg(true, 'All Role Details' , $get);
       
    }

    public function getUserByWorkflow(Request $request)
    {
        $request->validate([
            'workflowId' => 'required|int'
        ]);
        
        $users = new WfWorkflowrolemap();
        $getusers = $users->getUserByWorkflow($request);
        return responseMsg(true, 'All user Details', $getusers);

    }


    public function getWardsInWorkflow(Request $request)
    {
        try{
            $wards = new WfWorkflowrolemap();
            $getwards = $wards->getWardsInWorkflow($request);
            return responseMsg(true, 'All wards in Workflow', $getwards);
        }
        catch(Exception $e){
            return response()->json(false, $e->getmessage());

        }
    }


    public function getUlbInWorkflow(Request $request)
    {
        try{
            $ulb = new WfWorkflow();
            $getulb = $ulb->getUlbInWorkflow($request);
            return responseMsg(true, 'All Ulb in Workflow', $getulb);
        }
        catch(Exception $e){
            return response()->json(false, $e->getmessage());
        }
    }


    public function getWorkflowByRole(Request $request)
    {
        try{
            $workflow = new WfWorkflowrolemap();
            $getworkflow = $workflow->getWorkflowByRole($request);
            return responseMsg(true, 'Workflow By Role', $getworkflow);
        }
        catch(Exception $e){
            return response()->json(false, $e->getmessage());
        }
    }


    public function getUserByRoleId(Request $request)
    {
        try{
            $workflow = new WfRoleusermap();
            $getworkflow = $workflow->getUserByRoleId($request);
            return responseMsg(true, 'User By Role', $getworkflow);
        }
        catch(Exception $e){
            return responseMsg(false,$e->getmessage(),'');
        }
    }
    
    //workking
    //table = ulb_ward_master
    //ulbId->WardName
    //wards in ulb
    public function getWardByRole(Request $request)
    {
        
        try{
            $ward = new WfRoleusermap();
            $getward = $ward->getWardByRole($request);
            return responseMsg(true, 'Ward  By Role', $getward);
        }
        catch (Exception $e){
            return responseMsg(false, $e->getmessage(),'');
        }
    }


    public function getUlbByRole(Request $request)
    {
         try{
            $ulb = new WfWorkflowrolemap();
            $getulb = $ulb->getUlbyRole($request);
            return responseMsg(true, 'Ulb By Role', $getulb);
         }
         catch (Exception $e){
            return responseMsg(false, $e->getmessage(),'');
         }
    }

    


    



}
