<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Models\UlbWardMaster;
use Illuminate\Http\Request;

class WorkflowMap extends Controller
{
    //workking
    //table = ulb_ward_master
    //ulbId->WardName
    //wards in ulb
    public function getWardByUlb(Request $request)
    {
        //validating
        $request->validate([
            'ulbId' => 'nullable'
        ]);
        $ulbId = $request->ulbId ?? authUser()->ulb_id;
        $wards = collect();
        $workkFlow = UlbWardMaster::select(
            'id',
            'ulb_id',
            'ward_name',
            'old_ward_name'
        )
            ->where('ulb_id', $ulbId)
            ->where('status', 1)
            ->orderby('id')
            ->get();

        $groupByWards = $workkFlow->groupBy('ward_name');
        foreach ($groupByWards as $ward) {
            $wards->push(collect($ward)->first());
        }
        $wards->sortBy('ward_name')->values();
        return responseMsg(true, "Data Retrived", remove_null($wards));
    }
}
