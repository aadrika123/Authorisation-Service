<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomDetail;
use App\Models\ModuleMaster;
use App\Models\Property\PropProperty;
use App\Models\Property\PropSafGeotagUpload;
use App\Models\Property\PropSafVerification;
use App\Models\Property\PropTransaction;
use App\Models\QuickAccessMaster;
use App\Models\QuickaccessUserMap;
use App\Models\TcTracking;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\MicroServices\DocUpload;

class CustomController extends Controller
{
    public function getCustomDetails(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                "applicationId" => "required|numeric",
                "customFor" => "required|string"
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {

            $mCustomDetail = new CustomDetail();
            $customData = $mCustomDetail->getCustomDetails($request);

            return responseMsg(true, "Successfully Retreived", $customData);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //post custom details
    public function postCustomDetails(Request $request)
    {
        try {
            $validated = Validator::make(
                $request->all(),
                [
                    "applicationId" => "required|numeric",
                    "customFor" => "required|string",
                    'document' => "nullable|mimes:pdf,jpeg,png,jpg",
                    'remarks' => "nullable|regex:/^[a-zA-Z0-9\s]+$/",
                ]
            );
            if ($validated->fails()) {
                return validationError($validated);
            }
            $docUpload = new DocUpload;
            $path = Config::get('constants.CUSTOM_RELATIVE_PATH');
            $propertyModuleId = Config::get('constants.PROPERTY_MODULE_ID');
            $waterModuleId = Config::get('constants.WATER_MODULE_ID');
            $tradeModuleId = Config::get('constants.TRADE_MODULE_ID');
            $advertisementModuleId = Config::get('constants.ADVERTISEMENT_MODULE_ID');
            $customFor = trim(strtoupper($request->customFor));

            $customDetails = new CustomDetail;
            $filename    = NULL;
            $docRefNo    = NULL;
            $docUniqueId = NULL;
            $user = authUser();

            if ($file = $request->file('document')) {
                // $filename = time() .  '.' . $file->getClientOriginalExtension();
                $docResponse = $docUpload->checkDoc($request);
                $docUniqueId = $docResponse['data']['uniqueId'];
                $docRefNo    = $docResponse['data']['ReferenceNo'];
                // $file->move($path, $filename);
            }

            switch ($customFor) {
                case ('SAF'):
                    $request->merge([
                        "moduleId"    => $propertyModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"    => $docRefNo,
                        "ulbId"       => $user->ulb_id,
                        "customFor"   => $customFor,
                        "reftable"    => 'prop_active_safs',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('PROPERTY-CONCESSION'):
                    $request->merge([
                        "moduleId" => $propertyModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'prop_active_concessions',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('PROPERTY-OBJECTION'):
                    $request->merge([
                        "moduleId" => $propertyModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'prop_active_objections',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('PROPERTY-HARVESTING'):
                    $request->merge([
                        "moduleId" => $propertyModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'prop_active_harvestings',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('GBSAF'):
                    $request->merge([
                        "moduleId" => $propertyModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'prop_active_safs',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('PROPERTY DEACTIVATION'):
                    $request->merge([
                        "moduleId" => $propertyModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'prop_active_deactivation_requests',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('WATER'):
                    $request->merge([
                        "moduleId" => $waterModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'water_applications',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('TRADE'):
                    $request->merge([
                        "moduleId" => $tradeModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'active_trade_licences',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('SELF'):
                    $request->merge([
                        "moduleId" => $advertisementModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'adv_active_selfadvertisements',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('MOVABLE'):
                    $request->merge([
                        "moduleId" => $advertisementModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'adv_active_vehicles',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('PRIVATE'):
                    $request->merge([
                        "moduleId" => $advertisementModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'adv_active_privatelands',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('AGENCY'):
                    $request->merge([
                        "moduleId" => $advertisementModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'adv_active_agencies',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('HOARDING'):
                    $request->merge([
                        "moduleId" => $advertisementModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'adv_active_hoardings',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('BANQUET'):
                    $request->merge([
                        "moduleId" => $advertisementModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'mar_active_banqute_halls',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('LODGE'):
                    $request->merge([
                        "moduleId" => $advertisementModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'mar_active_lodges',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('HOSTEL'):
                    $request->merge([
                        "moduleId" => $advertisementModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'mar_active_hostels',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;

                case ('DHARAMSHALA'):
                    $request->merge([
                        "moduleId" => $advertisementModuleId,
                        "docUniqueId" => $docUniqueId,
                        "docRefNo"     => $docRefNo,
                        "ulbId"    => $user->ulb_id,
                        "customFor" => $customFor,
                        "reftable" => 'mar_active_dharamshalas',
                    ]);
                    $this->saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId);
                    break;
            }

            return responseMsg(true, "Successfully Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    public function saveCustomDetail($customDetails, $request, $docRefNo, $docUniqueId)
    {
        $mCustomDetail = new CustomDetail();

        $reqs = [
            'ref_id'        => $request->applicationId,
            'ref_type'      => $request->customFor,
            'doc_ref_no'    => $docRefNo,
            'doc_unique_id' => $docUniqueId,
            'remarks'       => $request->remarks,
            'module_id'     => $request->moduleId,
            'workflow_id'   => $request->workflowid,
            'ulb_id'        => $request->ulbId,
            'ref_table'     => $request->reftable,
            // 'type'          => "both",
        ];

        if ($request->remarks && $request->document) {
            $reqs['type'] = "both";
            // $reqs = [
            //     'ref_id'        => $request->applicationId,
            //     'ref_type'      => $request->customFor,
            //     'doc_ref_no'    => $docRefNo,
            //     'doc_unique_id' => $docUniqueId,
            //     'remarks'       => $request->remarks,
            //     'module_id'     => $request->moduleId,
            //     'workflow_id'   => $request->workflowid,
            //     'ulb_id'        => $request->ulbId,
            //     'ref_table'     => $request->reftable,
            //     'type'          => "both",
            // ];
            // $request->add(['type' => "both"]);
            // $mCustomDetail->saveCustomDetail($reqs);
        } elseif ($request->document) {
            $reqs['type'] = "file";

            // $customDetails->ref_id = $request->applicationId;
            // $customDetails->doc_ref_no = $docRefNo;
            // $customDetails->doc_unique_id = $docUniqueId;
            // $customDetails->type = "file";
        } elseif ($request->remarks) {
            $reqs['type'] = "text";

            // $customDetails->ref_id = $request->applicationId;
            // $customDetails->remarks = $request->remarks;
            // $customDetails->type = "text";
        }
        $mCustomDetail->saveCustomDetail($reqs);
    }

    /**
     * | Get Dues Api
     */
    public function duesApi(Request $request)
    {
        $mModuleMaster = new ModuleMaster();
        $duesApi = $mModuleMaster->duesApi();
        return responseMsgs(true, "Dues Api", $duesApi, "", 01, responseTime(), "POST", $request->deviceId);
    }

    /**
     * | Tc Geo Location
     */
    public function tcGeoLocation(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                "lattitude" => "required",
                "longitude" => "required"
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 422);
        }
        try {
            $userId = authUser($request)->id;
            $mTcTracking = new TcTracking();
            $mreqs = new Request([
                "user_id" => $userId,
                "lattitude" =>  $request->lattitude,
                "longitude" =>  $request->longitude,
            ]);
            $mTcTracking->store($mreqs);
            return responseMsgs(true, "location saved", "", "010203", "1.0", responseTime(), 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", responseTime(), 'POST', "");
        }
    }

    /**
     * | locationList
     */
    public function locationList(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                "date" => "required|date",
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 422);
        }
        try {
            $userId = $request->userId ?? authUser($request)->id;
            $mTcTracking = new TcTracking();
            $data = $mTcTracking->getLocationByUserId($userId, $request->date);
            return responseMsgs(true, "location list", $data, "010203", "1.0", responseTime(), 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "010203", "1.0", responseTime(), 'POST', "");
        }
    }

    /**
     * | Tc Route
     */
    // public function tcCollectionRoute(Request $request)
    // {
    //     $validate = Validator::make(
    //         $request->all(),
    //         ["date" => "required|date"]
    //     );
    //     if ($validate->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'validation error',
    //             'errors' => $validate->errors()
    //         ], 422);
    //     }
    //     try {
    //         $userId = $request->userId ?? authUser($request)->id;
    //         $mTcTracking = new TcTracking();
    //         $mPropTransaction = new PropTransactio();
    //         $tranDtls = $mPropTransaction->getPropTransactions($request->date, "tran_date");
    //         if ($tranDtls->isEmpty())
    //             throw new Exception('No Transaction Found Against this user');
    //         $tranDtls = collect($tranDtls)->where('user_id', $userId)->whereNotNull('property_id');
    //         $propIds = collect($tranDtls)->pluck('property_id');
    //         $propDtls = PropProperty::whereIn('id', $propIds)->get();
    //         if ($propDtls->isEmpty())
    //             throw new Exception('No Property Found');
    //         $safIds = collect($propDtls)->pluck('saf_id');
    //         $geoTag = PropSafGeotagUpload::select('saf_id', 'latitude', 'longitude')
    //             ->whereIn('saf_id', $safIds)
    //             ->where('direction_type', 'ilike', '%front%')
    //             ->get();

    //         return responseMsgs(true, "tc Route", $geoTag, "010203", "1.0", responseTime(), 'POST', "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", responseTime(), 'POST', "");
    //     }
    // }

    /**
     * | quickAccessList
     */
    public function quickAccessList(Request $request)
    {
        try {
            $mQuickAccessMaster = new QuickAccessMaster();
            $list = $mQuickAccessMaster->getList();

            return responseMsgs(true, "quickAccessList",  $list, "010203", "1.0", responseTime(), 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", responseTime(), 'POST', "");
        }
    }

    /**
     * | quickAccessList
     */
    public function getQuickAccessListByUser(Request $request)
    {
        try {
            $userId = $request->userId ?? authUser($request)->id;
            $mQuickaccessUserMap = new QuickaccessUserMap();
            $list = $mQuickaccessUserMap->getListbyUserId($userId);

            return responseMsgs(true, "Quick Access List by user Id",  $list, "010203", "1.0", responseTime(), 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", responseTime(), 'POST', "");
        }
    }

    /**
     * | Add Update Quick Access
     */
    public function addUpdateQuickAccess(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'items.*.quickAccessId' => 'required|integer',
                'items.*.status' => 'required|boolean',
            ]
        );
        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 422);
        }
        try {
            $user = authUser($request);
            $datas = $request->data;
            $mQuickaccessUserMap = new QuickaccessUserMap();
            foreach ($datas as $data) {

                $checkExisting = QuickaccessUserMap::where('user_id', $user->id)
                    ->where('quick_access_id', $data['quickAccessId'])
                    ->first();

                $mreqs = new Request([
                    "user_id" => $user->id,
                    "quick_access_id" => $data['quickAccessId'],
                    "status" => $data['status']
                ]);

                if ($checkExisting) {
                    $mreqs = $mreqs->merge(["id" => $checkExisting->id]);
                    $mQuickaccessUserMap->edit($mreqs);
                    $msg = "Quick Access Updated";
                } else {
                    $mQuickaccessUserMap->store($mreqs);
                    $msg = "Quick Access Addedd";
                }
            }

            return responseMsgs(true, $msg,  "", "010203", "1.0", responseTime(), 'POST', "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "010203", "1.0", responseTime(), 'POST', "");
        }
    }
}
