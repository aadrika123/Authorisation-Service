<?php

namespace App\Http\Controllers;

use App\Models\DistrictMaster;
use App\Models\MCity;
use App\Models\UlbMaster;
use App\Models\UlbNewWardmap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UlbController extends Controller
{
    /**
     * | Get All Ulbs
     */
    public function getAllUlb()
    {
        $ulb = UlbMaster::orderBy('ulb_name')
            ->where('active_status', true)
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
    #create
    public function createCity(Request $request)
    {
        try {
            $request->validate([
                "cityName" => "required",
                "stateId" => "required"
            ]);
            $create = new MCity();
            $create->createCity($request);

            return responseMsgs(true, "Add City", "", "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }
    #active and inactive city
    public function enableOrDesable(Request $req)
    {
        try {
            $delete = new MCity();
            $delete->activeOrDeatcive($req);

            return responseMsgs(true, "Data delete", "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }



    /**
     * | list of ulb by district code
     */
    public function districtWiseUlb(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            ['districtCode' => 'required']
        );
        if ($validated->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validated->errors()
            ], 422);
        }

        $mUlbMaster = new UlbMaster();
        $ulbList = $mUlbMaster->getUlbsByDistrictCode($req->districtCode);
        return responseMsgs(true, "", remove_null($ulbList));
    }

    /**
     * | District List
     */
    public function districtList(Request $req)
    {
        $districtList = DB::table('district_masters')
            ->where('status', true)
            ->orderBy('district_code')
            ->get();

        return responseMsgs(true, "", remove_null($districtList));
    }

    // Get All Ulb Wards
    public function getNewWardByOldWard(Request $req)
    {
        $req->validate([
            'oldWardMstrId' => 'required',
        ]);
        $mulbNewWardMap = new UlbNewWardmap();
        $newWard =  UlbNewWardmap::select(
            'ulb_new_wardmaps.id',
            'ulb_new_wardmaps.new_ward_mstr_id',
            'ward_name'
        )
            ->join('ulb_ward_masters', 'ulb_ward_masters.id', 'ulb_new_wardmaps.new_ward_mstr_id')
            ->where('old_ward_mstr_id', $req->oldWardMstrId)
            ->orderBy('new_ward_mstr_id')
            ->get();;

        return responseMsg(true, "Data Retrived", remove_null($newWard));
    }
    // add district 
    public function addDistrict(Request $request)
    {
        try {
            $mDistrictMaster = new DistrictMaster();
            $metarequest = [
                "district_code" => $request->districtCode,
                "distric_name"  => $request->districtName,
                "label"        =>  $request->label,
                "state_id"      => $request->stateId
            ];
            $data = $mDistrictMaster->addDistrict($request);
            return responseMsgs(true, "", remove_null($data), "Succesfully Add", "01", ".ms", "POST");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $e->getFile(), "", "01", ".ms", "POST",);
        }
    }
    /*
     * |edit agencgy details
     */
    public function updateDistrict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id"  => 'required'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $districtId = $request->id;
            $mDistrictMaster = new DistrictMaster();
            DB::beginTransaction();
            $mDistrictMaster->updatesDistrictDtl($request, $districtId);
            DB::commit();
            return responseMsgs(true, "Update District  !!",  "050501", "1.0", responseTime(), 'POST', $request->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(true, $e->getMessage(), "", "050501", "1.0", "", "POST", $request->deviceId ?? "");
        }
    }
    //delete master
    public function deleteDistrict(Request $req)
    {
        try {
            $delete = new DistrictMaster();
            $delete->deleteDistrict($req);

            return responseMsgs(true, "", "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
    #get district 
    public function getDistrictdtl(){
        $data= DistrictMaster::select('id','district_code','district_name','status as is_suspended')
        ->get();
        return responseMsgs(true, "", remove_null($data));
}
}
