<?php

namespace App\Models\Workflows;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\IgnoreFunctionForCodeCoverage;

class WfWorkflow extends Model
{
    use HasFactory;

    //create workflow
    public function addWorkflow($req)
    {
        $createdBy = Auth()->user()->id;
        $data = new WfWorkflow;
        $data->wf_master_id = $req->wfMasterId;
        $data->ulb_id = $req->ulbId;
        $data->alt_name = $req->altName;
        $data->is_doc_required = $req->isDocRequired;
        $data->created_by = $createdBy;
        $data->initiator_role_id = $req->initiatorRoleId;
        $data->finisher_role_id = $req->finisherRoleId;
        $data->stamp_date_time = Carbon::now();
        $data->created_at = Carbon::now();
        $data->save();

        // Auto-copy role mappings from template (ulb_id = 2)
        $this->copyRoleMappingsFromTemplate($data->id, $req->altName);
    }

    /**
     * Copy role mappings from template ULB (ulb_id = 2) to new workflow
     */
    private function copyRoleMappingsFromTemplate($newWorkflowId, $altName)
    {
        // Find template workflow with same alt_name in ulb_id = 2
        $templateWorkflow = WfWorkflow::where('ulb_id', 2)
            ->where('alt_name', $altName)
            ->where('is_suspended', false)
            ->first();

        if (!$templateWorkflow) {
            return; // No template found, skip copying
        }

        // Get all role mappings from template workflow
        $templateRoleMaps = WfWorkflowrolemap::where('workflow_id', $templateWorkflow->id)
            ->where('is_suspended', false)
            ->get();

        // Copy each role mapping to new workflow
        foreach ($templateRoleMaps as $roleMap) {
            DB::table('wf_workflowrolemaps')->insert([
                'workflow_id' => $newWorkflowId,
                'wf_role_id' => $roleMap->wf_role_id,
                'is_suspended' => $roleMap->is_suspended,
                'forward_role_id' => $roleMap->forward_role_id,
                'backward_role_id' => $roleMap->backward_role_id,
                'is_initiator' => $roleMap->is_initiator,
                'is_finisher' => $roleMap->is_finisher,
                'allow_full_list' => $roleMap->allow_full_list,
                'can_escalate' => $roleMap->can_escalate,
                'serial_no' => $roleMap->serial_no,
                'is_btc' => $roleMap->is_btc,
                'is_enabled' => $roleMap->is_enabled,
                'can_view_document' => $roleMap->can_view_document,
                'can_upload_document' => $roleMap->can_upload_document,
                'can_verify_document' => $roleMap->can_verify_document,
                'allow_free_communication' => $roleMap->allow_free_communication,
                'can_forward' => $roleMap->can_forward,
                'can_backward' => $roleMap->can_backward,
                'is_pseudo' => $roleMap->is_pseudo,
                'show_field_verification' => $roleMap->show_field_verification,
                'can_view_form' => $roleMap->can_view_form,
                'can_see_tc_verification' => $roleMap->can_see_tc_verification,
                'can_edit' => $roleMap->can_edit,
                'can_send_sms' => $roleMap->can_send_sms,
                'can_comment' => $roleMap->can_comment,
                'is_custom_enabled' => $roleMap->is_custom_enabled,
                'je_comparison' => $roleMap->je_comparison,
                'technical_comparison' => $roleMap->technical_comparison,
                'can_view_technical_comparison' => $roleMap->can_view_technical_comparison,
                'associated_workflow_id' => $roleMap->associated_workflow_id,
                'created_by' => Auth()->user()->id,
                'stamp_date_time' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    //update workflow
    public function updateWorkflow($req)
    {
        $data = WfWorkflow::find($req->id);
        $data->wf_master_id = $req->wfMasterId;
        $data->ulb_id = $req->ulbId;
        $data->alt_name = $req->altName;
        $data->is_doc_required = $req->isDocRequired;
        $data->initiator_role_id = $req->initiatorRoleId;
        $data->finisher_role_id = $req->finisherRoleId;
        $data->save();
    }

    //list workflow by id
    public function listbyId($req)
    {
        $data = WfWorkflow::where('id', $req->id)
            ->where('is_suspended', false)
            ->first();
        return $data;
    }

    //All workflow list
    public function listUlbWorkflow()
    {
        $data = WfWorkflow::select(
            'wf_workflows.*',
            'wf_masters.workflow_name',
            'ulb_masters.ulb_name',
            'wf_roles.role_name as initiator_role_name',
            'frole.role_name as finisher_role_name'
        )
            ->join('wf_masters', 'wf_masters.id', 'wf_workflows.wf_master_id')
            ->join('ulb_masters', 'ulb_masters.id', 'wf_workflows.ulb_id')
            ->leftJoin('wf_roles', 'wf_roles.id', 'wf_workflows.initiator_role_id')
            ->leftJoin('wf_roles as frole', 'frole.id', 'wf_workflows.finisher_role_id')
            ->where('wf_workflows.is_suspended', false)
            // ->where('wf_workflows.ulb_id', $ulbId)
            ->orderByDesc('wf_workflows.id')
            ->get();
        return $data;
    }

    /**
     * Delete workflow
     */
    public function deleteWorkflow($req)
    {
        $data = WfWorkflow::find($req->id);
        $data->is_suspended = true;
        $data->save();
    }

    public function getUlbInWorkflow($request)
    {

        $users = WfWorkflow::where('wf_master_id', $request->id)
            ->select('ulb_masters.*')
            ->join('ulb_masters', 'ulb_masters.id', '=', 'wf_workflows.ulb_id')
            ->get();
        return $users;
    }

    /**
     * | Get Workflow by ulbId
     */
    public function getWorklowByUlbId($ulbId)
    {
        return WfWorkflow::where('ulb_id', $ulbId)
            ->where('is_suspended', false)
            ->get();
    }

    /**
     * | Get Workflow List by Module
     */
    public function  workflowbyModule($moduleId)
    {
        return WfWorkflow::select('wf_workflows.id', 'workflow_name')
            ->join('wf_masters', 'wf_masters.id', 'wf_workflows.wf_master_id')
            ->where('module_id', $moduleId)
            ->where('wf_workflows.is_suspended', false)
            ->get();
    }
}
