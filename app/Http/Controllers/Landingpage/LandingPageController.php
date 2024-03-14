<?php

namespace App\Http\Controllers\Landingpage;

use App\Http\Controllers\Controller;
use App\Models\Landingpage\LandingPage;
use App\Models\Landingpage\Scheme;
use App\Models\Landingpage\SchemeType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LandingPageController extends Controller
{
    /**
     * | Get Scheme Type List
     */
    public function getListSchemeType(Request $req)
    {
        $mSchemeType = new SchemeType();
        $list = $mSchemeType->listSchemeTypes();
        return responseMsg(true, 'Scheme List', $list);
    }
    /**
     * add scheme type
     */
    public function addSchemeType(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'type' => 'required',
            ]);
            if ($validator->fails()) {
                return responseMsgs(false, $validator->errors(), "", "050511", "1.0", "", "POST", $req->deviceId ?? "");
            }
            $mScheme = new SchemeType();
            $status = $mScheme->addSchemeType($req);                                                 //<--------------- Model function to store 
            return responseMsgs(true, "Successfully Added", ['Details' => $status], 055102, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050516", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }
    #update scheme type
    public function updateSchemeType(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                "id"   => 'required',
                'type' => 'required',
            ]);
            if ($validator->fails()) {
                return responseMsgs(false, $validator->errors(), "", "050511", "1.0", "", "POST", $req->deviceId ?? "");
            }
            $mScheme = new SchemeType();
            $status = $mScheme->updateSchemeType($req);                                                 //<--------------- Model function to store 
            return responseMsgs(true, "Successfully updated", ['Details' => $status], 055102, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050516", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }
    #get scheme type by id 
    public function getSchemeTypeById(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                "id"   => 'required',
            ]);
            if ($validator->fails()) {
                return responseMsgs(false, $validator->errors(), "", "050511", "1.0", "", "POST", $req->deviceId ?? "");
            }
            $mScheme = new SchemeType();
            $data = $mScheme->getSchemeTypeByid($req);                                                 //<--------------- Model function to store 
            return responseMsgs(true, "Data get ", ['Details' => $data], 055102, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050516", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }
     #get  
     public function getSchemetype(){
        $data= SchemeType::select('id','type','status as is_suspended')
        ->get();
        return responseMsgs(true, "", remove_null($data));
}
    // public function updateSchemeType(Request $req)
    // {
    //     try {
    //         $validator = Validator::make($req->all(), [
    //             "id"   => 'required',
    //             'type' => 'required',
    //         ]);
    //         if ($validator->fails()) {
    //             return responseMsgs(false, $validator->errors(), "", "050511", "1.0", "", "POST", $req->deviceId ?? "");
    //         }
    //         $mScheme = new SchemeType();
    //         $status = $mScheme->updateSchemeType($req);                                                 //<--------------- Model function to store 
    //         return responseMsgs(true, "Successfully updated", ['Details' => $status], 055102, "1.0", responseTime(), "POST", $req->deviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "050516", "1.0", "", 'POST', $req->deviceId ?? "");
    //     }
    // }
    /**
     * | Add Scheme
     */
    public function addScheme(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'schemeTypeId' => 'required|integer',
                'content' => 'required|string',
                'link' => 'nullable|url',
            ]);
            if ($validator->fails()) {
                return responseMsgs(false, $validator->errors(), "", "050511", "1.0", "", "POST", $req->deviceId ?? "");
            }
            $mScheme = new Scheme();
            $status = $mScheme->addScheme($req);                                                 //<--------------- Model function to store 
            return responseMsgs(true, "Successfully Added", ['Details' => $status], 055102, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050516", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    public function listAllScheme(Request $req)
    {
        try {
            $mScheme = new Scheme();
            $list = $mScheme->listAllScheme();
            if ($req->schemeId)
                $list1 = $list->where('schemes.id', $req->schemeId)->get();
            $list1 = $list->get();
            return responseMsgs(true, "List Fectch Successfully", ['Details' => $list1], 055102, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050516", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    public function deleteScheme(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'schemeId' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return responseMsgs(false, $validator->errors(), "", "050511", "1.0", "", "POST", $req->deviceId ?? "");
            }
            $mScheme = new Scheme();
            $scheme = $mScheme->find($req->schemeId);
            if (!$scheme)
                throw new Exception("Data Not Found !!!");
            $scheme->status = 0;
            $scheme->save();                                              //<--------------- Model function to store 
            return responseMsgs(true, "Scheme Deleted Successfully !!!", '', 055102, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050516", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }


    public function editScheme(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'schemeId' => 'required|integer',
                'schemeTypeId' => 'required|integer',
                'content' => 'required|string',
                'link' => 'nullable|url',
            ]);
            if ($validator->fails()) {
                return responseMsgs(false, $validator->errors(), "", "050511", "1.0", "", "POST", $req->deviceId ?? "");
            }
            $mScheme = new Scheme();
            $scheme = $mScheme->find($req->schemeId);
            if (!$scheme)
                throw new Exception("Data Not Found !!!");

            $scheme->scheme_type_id = $req->schemeTypeId;
            $scheme->content = $req->content;
            $scheme->link = $req->link;
            $scheme->save();                                                             
            return responseMsgs(true, "Scheme Updated Successfully !!!", '', 055102, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050516", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }

    public function listTypeWiseScheme(Request $req){
        try {
            $mScheme = new Scheme();
            $list = $mScheme->listAllScheme()->get();
            $list1 = collect($list)->groupBy('scheme_type');
            return responseMsgs(true, "List Fectch Successfully", $list1, 055102, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "050516", "1.0", "", 'POST', $req->deviceId ?? "");
        }
    }
}
