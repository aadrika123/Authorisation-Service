<?php

namespace App\Http\Controllers;

use App\Models\ZoneMaster;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ZoneMasterController extends Controller
{
    #create
    public function createZone(Request $request)
    {
        try {
            $request->validate([
                "zone" => "required",
                "ulbId" => "required"
            ]);
            $create = new ZoneMaster();
            $create->addZone($request);

            return responseMsgs(true, "Add Zone", "", "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }
     //all master list
     public function getZone(Request $req)
     {
         try {
             $ulbId = authUser()->ulb_id;
             $list = new ZoneMaster();
             $Zone = $list->listOfZone($ulbId);
 
             return responseMsgs(true, "All Workflow List", $Zone, "120204", "01", responseTime(), $req->getMethod(), $req->deviceId);
         } catch (Exception $e) {
             return responseMsgs(false, $e->getMessage(), "", "120204", "01", responseTime(), $req->getMethod(), $req->deviceId);
         }
     }
      //delete master
    public function deleteZone(Request $req)
    {
        try {
            $delete = new ZoneMaster();
            $delete->deleteWorkflow($req);

            return responseMsgs(true, "Data Deleted", "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
}
