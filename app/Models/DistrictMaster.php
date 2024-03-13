<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistrictMaster extends Model
{
    use HasFactory;

    public function addDistrictV2($metarequest)
    {
        $district = DistrictMaster::create($metarequest);
        return $district->id;
    }
    public function addDistrict($req)
    {
        $data = new DistrictMaster();
        $data->district_code = $req->districtCode;
        $data->district_name = $req->districtName;
        $data->label = $req->label;
        $data->state_id = $req->stateId;
        $data->save();
        return $data->id;
    }
    #update district details
    public function updatesDistrictDtl($req, $districtId)
    {
        $district = DistrictMaster::where('id', $districtId)->firstOrFail();
        $district->district_code = $req->districtCode;
        $district->district_name = $req->districtName;
        $district->label = $req->label;
        $district->state_id = $req->stateId;
        $district->update();
        return $district->id;
    }
     /**
     * Delete district
     */
   /**
 * Delete district
 */
public function deleteDistrict($req)
{
    $data = DistrictMaster::find($req->id);

    if ($req->status == 1) {
        // If status is 1, set status to true (active)
        $data->status = true;
    } else {
        // If status is not 1, set status to false (inactive)
        $data->status = false;
    }
    // Save the changes to the database
    $data->save();
}

}
