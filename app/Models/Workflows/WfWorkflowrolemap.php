<?php

namespace App\Models\Workflows;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WfWorkflowrolemap extends Model
{
    use HasFactory;

    public function getRoleDetails($request ){
    
    //$roleDetails = new WfWorkflowrolemap();    
     $roleDetails = DB::table('wf_workflowrolemaps')
            ->select(
                'wf_workflowrolemaps.id',
                'wf_workflowrolemaps.workflow_id',
                'wf_workflowrolemaps.wf_role_id',
                'wf_workflowrolemaps.forward_role_id',
                'wf_workflowrolemaps.backward_role_id',
                'wf_workflowrolemaps.is_initiator',
                'wf_workflowrolemaps.is_finisher',
                'r.role_name as forward_role_name',
                'rr.role_name as backward_role_name'
            )
            ->leftJoin('wf_roles as r', 'wf_workflowrolemaps.forward_role_id', '=', 'r.id')
            ->leftJoin('wf_roles as rr', 'wf_workflowrolemaps.backward_role_id', '=', 'rr.id')
            ->where('workflow_id', $request->workflowId)
            ->where('wf_role_id', $request->wfRoleId)
            ->orderBy('role_id')
            ->first();

            return $roleDetails;
    }
    

   //Role by Workflow
    public function getRoleByWorkflow($request, $ulbId){

    $roles = WfWorkflowrolemap::select('wf_roles.id as role_id', 'wf_roles.role_name')
            ->join('wf_roles', 'wf_roles.id', '=', 'wf_workflowrolemaps.wf_role_id')
            ->join('wf_workflows', 'wf_workflows.id', 'wf_workflowrolemaps.workflow_id')
            ->where('wf_workflows.ulb_id', $ulbId)
            ->where('workflow_id', $request->workflowId)
            ->where(function ($where) {
                $where->orWhereNotNull("wf_workflowrolemaps.forward_role_id")
                    ->orWhereNotNull("wf_workflowrolemaps.backward_role_id")
                    ->orWhereNotNull("wf_workflowrolemaps.serial_no");
            })
            ->orderBy('serial_no')
            ->get();

        return $roles;

}    

    public function getUserByWorkflow($request){
        
        $users = WfWorkflowrolemap::where('workflow_id', $request->workflowId)
            ->select('user_name', 'mobile', 'email', 'user_type')
            ->join('wf_roles', 'wf_roles.id', '=', 'wf_workflowrolemaps.wf_role_id')
            ->join('wf_roleusermaps', 'wf_roleusermaps.wf_role_id', '=', 'wf_roles.id')
            ->join('users', 'users.id', '=', 'wf_roleusermaps.user_id')
            ->get();

        return $users;
    }


    public function getWardsInWorkflow($request)
        {
        $users = WfWorkflowrolemap::select('ulb_ward_masters.ward_name', 'ulb_ward_masters.id')
            ->where('workflow_id', $request->workflowId)
            ->join('wf_roles', 'wf_roles.id', '=', 'wf_workflowrolemaps.wf_role_id')
            ->join('wf_roleusermaps', 'wf_roleusermaps.wf_role_id', '=', 'wf_roles.id')
            ->join('wf_ward_users', 'wf_ward_users.user_id', '=', 'wf_roleusermaps.user_id')
            ->join('ulb_ward_masters', 'ulb_ward_masters.id', '=', 'wf_ward_users.ward_id')
            ->orderBy('id')
            ->get();    

        return $users;
    }

    
    public function getWorkflowByRole($request)
    {
        $users = WfWorkflowrolemap::where('wf_role_id', $request->roleId)
            ->select('workflow_name')
            ->join('wf_workflows', 'wf_workflows.id', '=', 'wf_workflowrolemaps.workflow_id')
            ->join('wf_masters', 'wf_masters.id', '=', 'wf_workflows.wf_master_id')
            ->get();
        return $users;
    }

}
