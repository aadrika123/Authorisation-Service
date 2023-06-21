<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WfWorkflow extends Model
{
    use HasFactory;

    public Function getUlbInWorkflow($request){

        $users = WfWorkflow::where('wf_master_id', $request->id)
            ->select('ulb_masters.*')
            ->join('ulb_masters', 'ulb_masters.id', '=', 'wf_workflows.ulb_id')
            ->get();
        return $users;
    }
}
