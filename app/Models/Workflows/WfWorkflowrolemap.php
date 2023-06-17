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


}
