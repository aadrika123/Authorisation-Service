<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneMaster extends Model
{
    use HasFactory;

    public function addZone($req)
    {
        $data = new ZoneMaster;
        $data->zone = $req->zone;
        $data->ulb_id = $req->ulbId;
        $data->save();
    }
    //All workflow list
    public function listOfZone($ulbId)
    {
        $data = ZoneMaster::select(
            'zone_masters.*',
            'ulb_masters.ulb_name'
        )
            ->join('ulb_masters', 'ulb_masters.id', 'zone_masters.ulb_id')
            ->where('zone_masters.status', true)
            ->where('zone_masters.ulb_id', $ulbId)
            ->orderByDesc('zone_masters.id')
            ->get();
        return $data;
    }
    #active or inactive
    public function deleteWorkflow($req)
    {
        $data = ZoneMaster::find($req->id);

        if ($req->status == 1) {
            // If status is 1, set status to true (active)
            $data->status = true;
        } else {
            // If status is not 1, set status to false (inactive)
            $data->status = false;
        }
        $data->save();
    }
}
