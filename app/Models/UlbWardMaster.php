<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UlbWardMaster extends Model
{
    use HasFactory;

    public function getWardByUlb($ulbId)
    {
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

        return $workkFlow;
    }
    # create ulb ward
    public function addUlbWard($req)
    {
        $data = new UlbWardMaster;
        $data->ulb_id = $req->ulbId;
        $data->ward_name = $req->wardNumber;
        $data->new_assessment_counter = 1;
        $data->re_assessment_counter = 1;
        $data->mutation_assessment_counter = 1;
        $data->bifurcation_assessment_counter = 1;
        $data->sam_counter = 1;
        $data->fam_counter = 1;
        $data->holding_counter = 1;
        $data->save();
        return $data->id;
    }
    # get all data
    public function getALL()
    {
        $data = UlbWardMaster::select(
            'ulb_ward_masters.id',
            'ulb_ward_masters.ward_name as ward_number',
            'ulb_ward_masters.ulb_id',
            'ulb_ward_masters.status as is_suspended',
            'ulb_masters.ulb_name'
        )
            ->join('ulb_masters', 'ulb_masters.id', 'ulb_ward_masters.ulb_id')
            ->orderByDesc('ulb_ward_masters.id')
            ->get();
        return $data;
    }
    #active or inactive
    public function activeDeactive($req)
    {
        $data = UlbWardMaster::find($req->id);

        if ($req->status == 1) {
            // If status is 1, set status to true (active)
            $data->status = 1;
            $message = 'Data activated';
        } else {
            // If status is not 1, set status to false (inactive)
            $data->status = 0;
            $message = 'Data deactivated';
        }
        $data->save();
        return $message;
    }
    # create ulb ward
    public function updateZoneById($req)
    {
        $data = UlbWardMaster::find($req->id);
        $data->ulb_id = $req->ulbId;
        $data->ward_name = $req->wardNumber;
        $data->new_assessment_counter = 1;
        $data->re_assessment_counter = 1;
        $data->mutation_assessment_counter = 1;
        $data->bifurcation_assessment_counter = 1;
        $data->sam_counter = 1;
        $data->fam_counter = 1;
        $data->holding_counter = 1;
        $data->save();
        return $data->id;
    }
    #get data by id
    public function getDataByIdDtls($req)
    {
        $data = UlbWardMaster::select(
            'ulb_ward_masters.id',
            'ulb_ward_masters.ward_name as ward_number',
            'ulb_ward_masters.ulb_id',
            'ulb_ward_masters.status as is_suspended',
            'ulb_masters.ulb_name'
        )
            ->leftjoin('ulb_masters', 'ulb_masters.id', 'ulb_ward_masters.ulb_id')
            ->where('ulb_ward_masters.id', $req->id)
            ->orderby('id')
            ->first();

        return $data;
    }
}
