<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\UlbMaster;

class MCity extends Model
{
    use HasFactory;

    /**
     * | Get City and State by Ulb Id
     */
    public function getCityStateByUlb($ulbId)
{
    return UlbMaster::select("ulb_masters.*", "c.city_name", "s.name")
        ->join("m_cities as c", 'c.id', '=', 'ulb_masters.city_id')
        ->join("m_states as s", "s.id", '=', 'c.state_id')
        ->where('c.status', 1)
        ->where("ulb_masters.id", $ulbId)
        ->first();
}

    public function createCity($req)
    {
        $data = new MCity;
        $data->city_name = $req->cityName;
        $data->state_id = $req->stateId;
        $data->save();
    }
    #active or inactive
    public function activeOrDeatcive($req)
    {
        $data = MCity::find($req->id);

        if ($req->status == 1) {
            // If status is 1, set status to true (active)
            $data->status = 1;
        } else {
            // If status is not 1, set status to false (inactive)
            $data->status = 0;
        }
        $data->save();
    }
    #update city
    public function updateCityById($req)
    {
        $data = MCity::find($req->id);
        $data->city_name = $req->cityName;
        $data->state_id = $req->stateId;
        $data->save();
    }
}
