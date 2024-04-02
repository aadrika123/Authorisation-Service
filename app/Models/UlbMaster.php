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
            // ->where('active_status', true)
            ->get();
    }
    /**
     * |deactive or active ulb
     */
    public function deactiveUlb($req)
    {

        $data = UlbMaster::find($req->id);

        if ($req->status == 1) {
            $data->active_status = false;
        } else {
            $data->active_status = true;
        }
        // Save the changes to the database
        $data->save();
    }
    #get data by id
    public function getDataByIdDtls($request)
    {
        return self::select(
            'id',
            'ulb_name',
            'city_id',
            'state_id',
            'department_id',
            'active_status as is_suspended'
        )
            ->where('id', $request->id)
            ->first();
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
        $data->city_id = $req->cityId;
        $data->incorporation_date = $req->incorporationDate;
        $data->department_id = $req->departmentId;
        $data->district_code = $req->districtCode;
        $data->category = $req->category;
        $data->code = $req->code;
        $data->short_name = $req->shortName;
        $data->toll_free_no = $req->tollFreeNo;
        $data->current_website = $req->currentWebsite;
        $data->parent_website = $req->parentWebsite;
        $data->email = $req->email;
        $data->mobile_no = $req->mobileNo;
        $data->state_id = $req->stateId;
        $data->district_id = $req->districtId;
        $data->association_with = $req->associationWith;
        $data->latitude = $req->latitude;
        $data->longitude = $req->longitude;
        $data->active_status = true;
        $data->save();
        return $data->id;
    }
    /**
     * |update ulb master
     */
    public function updateUlbById($req)
    {
        $data = MCity::find($req->id);
        $data->ulb_name = $req->ulbName;
        $data->ulb_type = $req->ulbType;
        $data->remarks = $req->remarks;
        $data->city_id = $req->cityId;
        $data->incorporation_date = $req->incorporationDate;
        $data->department_id = $req->departmentId;
        $data->district_code = $req->districtCode;
        $data->category = $req->category;
        $data->code = $req->code;
        $data->short_name = $req->shortName;
        $data->toll_free_no = $req->tollFreeNo;
        $data->current_website = $req->currentWebsite;
        $data->parent_website = $req->parentWebsite;
        $data->email = $req->email;
        $data->mobile_no = $req->mobileNo;
        $data->state_id = $req->stateId;
        $data->district_id = $req->districtId;
        $data->association_with = $req->associationWith;
        $data->latitude = $req->latitude;
        $data->longitude = $req->longitude;
        $data->save();
        return $data->id;
    }
}
