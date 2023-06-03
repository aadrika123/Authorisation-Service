<?php

namespace App\Http\Controllers;

use App\Models\MCity;
use App\Models\UlbMaster;
use Exception;
use Illuminate\Http\Request;

class UlbController extends Controller
{
    /**
     * | Get All Ulbs
     */
    public function getAllUlb()
    {
        $ulb = UlbMaster::orderBy('ulb_name')
            ->get();
        return responseMsgs(true, "", remove_null($ulb));
    }


    /**
     * | Get City State by Ulb Id
     */
    public function getCityStateByUlb(Request $req)
    {
        if (!$req->bearerToken()) {
            $req->validate([
                'ulbId' => 'required|integer'
            ]);
        }
        try {
            $ulbId = $req->ulbId ?? authUser()->ulb_id;
            $mCity = new MCity();
            $data = $mCity->getCityStateByUlb($ulbId);
            return responseMsgs(true, "", remove_null($data));
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
}
