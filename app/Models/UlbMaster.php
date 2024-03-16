<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UlbMaster extends Model
{
    use HasFactory;

    /**
     * | Get Ulbs by district code
     */
    public function getUlbsByDistrictCode($districtCode)
    {
        return UlbMaster::where('district_code', $districtCode)
            ->get();
    }
    #get all ulb
    public function getAll()
    {
        return UlbMaster::select('id', 'ulb_name', 'active_status as is_suspended')
            ->where('active_status', true)
            ->get();
    }
    /**
     * |deactive or active ulb
     */
    public function deactiveUlb($req)
    {

        $data = UlbMaster::find($req->id);

        if ($req->status == 1) {
            $data->active_status = true;
        } else {
            $data->active_status = false;
        }
        // Save the changes to the database
        $data->save();
    }
    /**
     * |add ulb master
     */
    public function addUlbMaster($req)
    {
        $data = new UlbMaster();
        $data->ulb_name = $req->ulbName;
        $data->ulb_type = $req->ulbType;
        $data->remarks = $req->remarks;
        $data->department_id = $req->stateId;
        $data->district_code = $req->district_code;
        $data->category = $req->category;
        $data->code = $req->code;
        $data->short_name = $req->short_name;
        $data->state_id = $req->state_id;
        $data->district_id = $req->district_id;
        $data->association_with = $req->association_with;
        $data->latitude = $req->latitude;
        $data->save();

        return $data->id;
    }
}
