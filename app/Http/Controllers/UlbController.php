<?php

namespace App\Http\Controllers;

use App\MicroServices\DocUpload;
use App\Models\DistrictMaster;
use App\Models\MCity;
use App\Models\ServiceMapping;
use App\Models\ServiceMaster;
use App\Models\UlbMaster;
use App\Models\UlbModulePermission;
use App\Models\UlbNewWardmap;
use App\Models\UlbService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UlbController extends Controller
{
    protected $_UlbModulePermission;
    protected $_UlbServices;
    public function __construct()
    {
        $this->_UlbModulePermission = new UlbModulePermission();
        $this->_UlbServices = new ServiceMapping();
    }

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
     * |Get All Ulb 
     */
    public function getAllUlbDtls()
    {
        $ulb = UlbMaster::select(
            'ulb_masters.*',
            'active_status as is_suspended',
        )
            ->orderBy('ulb_name')
            ->get();
        return responseMsgs(true, "", remove_null($ulb));
    }

    /**
     * |active or deactive ulb_masters by id
     */
    public function deactiveUlbById(Request $req)
    {
        try {
            $delete = new UlbMaster();
            $delete->deactiveUlb($req);

            return responseMsgs(true, "", "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
    /**
     * |add ulb masters
     */
    public function createUlbmaster(Request $req)
    {
        try {
            $create = new UlbMaster();
            $ulbId = $create->addUlbMaster($req);
            return responseMsgs(true, "", $ulbId, "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
    # get ulb master by id 
    public function getulbById(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                "id" => 'required'
            ]
        );
        if ($validated->fails())
            return validationError($validated);
        try {
            $mCity = new UlbMaster();
            $data = $mCity->getDataByIdDtls($request);
            return responseMsgs(true, "Data ", $data, "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }
    /**
     * |update ulb master
     */
    public function updateUlbId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "id"  => 'required'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mCity = new UlbMaster();
            $update = $mCity->updateUlbById($req);
            return responseMsgs(true, "Data updated", "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
    #===========================Crud for city table============================#
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
    #get all city data
    public function getCity(Request $req)
    {
        try {
            $mCity = new MCity();
            $data = $mCity->getaAllData();
            return responseMsgs(true, "", remove_null($data));
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
    #get data by id 
    public function getByIdCiTy(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                "id" => 'required'
            ]
        );
        if ($validated->fails())
            return validationError($validated);
        try {
            $mCity = new MCity();
            $data = $mCity->getDataByIdDtls($request);
            return responseMsgs(true, "Data ", $data, "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }
    #create city
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
     * |update city 
     */
    public function updateCity(Request $req)
    {
        $validator = Validator::make($req->all(), [
            "id"  => 'required'
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => $validator->errors()];
        }
        try {
            $mCity = new MCity();
            $update = $mCity->updateCityById($req);
            return responseMsgs(true, "Data updated", "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
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
            return responseMsgs(true, "Add District Successfully", remove_null($data), "Succesfully Add", "01", ".ms", "POST");
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
    #get district by Id
    public function getDistrictById(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                "id" => 'required'
            ]
        );
        if ($validated->fails())
            return validationError($validated);
        try {
            $mCity = new DistrictMaster();
            $data = $mCity->getDataByIdDtls($request);
            return responseMsgs(true, "Data ", $data, "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120201", "01", responseTime(), $request->getMethod(), $request->deviceId);
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
    public function getDistrictdtl()
    {
        $data = DistrictMaster::select('id', 'district_code', 'district_name', 'status as is_suspended')
            ->get();
        return responseMsgs(true, "", remove_null($data));
    }
    /**
     * |STATE
     */
    public function getAllState(Request $req)
    {
        $data = DB::table('m_state')
            ->get();
        return responseMsgs(true, "", remove_null($data));
    }
    #########################################################################################################################################
    /**
     Functions For  Make Ulb Wise Module Permissions
     created on = 2025-01-07
     created by = Arshad Hussain 
     */

    //create WardUser
    public function createModuleUlb(Request $req)
    {

        $validated = Validator::make(
            $req->all(),
            [
                'ulbId' => 'required',
                'moduleList' => 'required|array',
                // 'permissionStatus'=>'required
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $ulbId           = $req->ulbId;
            $moduleId         = $req->moduleList;


            collect($moduleId)->map(function ($item) use ($ulbId) {

                $mUlbModulePermission = $this->_UlbModulePermission;
                $checkExisting = $mUlbModulePermission::where('ulb_id', $ulbId)
                    ->where('module_id', $item['moduleId'])
                    ->first();

                if ($item['permissionStatus'] == 0)
                    $isSuspended = true;

                if ($item['permissionStatus'] == 1)
                    $isSuspended = false;

                if ($checkExisting) {

                    $req = new Request([
                        'id' => $checkExisting->id,
                        'ulbId' => $ulbId,
                        'moduleId' => $item['moduleId'],
                        'isSuspended' => $isSuspended,
                    ]);

                    $mUlbModulePermission->updateModuleUlb($req);
                } else {
                    $req = new Request([
                        'ulbId' => $ulbId,
                        'moduleId' => $item['moduleId'],
                        'isSuspended' => $isSuspended,
                    ]);
                    $mUlbModulePermission->addModuleUlb($req);
                }
            });

            // $checkExisting = WfWardUser::where('user_id', $req->userId)
            //     ->where('ward_id', $req->wardId)
            //     ->first();

            // if ($checkExisting)
            //     throw new Exception("User Exist");

            // $mWfWardUser = new WfWardUser();
            // $mWfWardUser->addWardUser($req);

            return responseMsg(true, "Successfully Saved", "");
        } catch (Exception $e) {
            return responseMsg(true,  $e->getMessage(), "");
        }
    }

    public function ulbModuleList(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            ['ulbId'     => 'required|int',]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $mUlbModulePermission = $this->_UlbModulePermission;
            $user = authUser();

            $query = "select 
                            mm.id,
                            um.ulb_id,
                            mm.module_name as module_name,
                            mm.url,
                            um.created_by,
                            case 
                                when um.module_id is null then false
                                else
                                    true  
                            end as permission_status
                        from module_masters as mm
                        left join (select * from ulb_module_permissions where ulb_id=$req->ulbId and is_suspended = false) as um on um.module_id=mm.id
                        order by um.id";

            $data = DB::select($query);
            return responseMsg(true, "Module List of Ulb", $data);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
    //  get list of module list by ulb id 
    public function getMoudleByUlbId(Request $req)
    {
        try {
            $req->validate([
                "ulbId" => "required",
            ]);
            $mUlbModulePermission = $this->_UlbModulePermission;
            $data = $mUlbModulePermission->getModuleListByUlbId($req);
            return responseMsgs(true, "Data ", $data, "120201", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120205", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Manage services with respect to among ulb wise 
    public function createServicesUlb(Request $req)
    {

        $validated = Validator::make(
            $req->all(),
            [
                'ulbId' => 'required',
                'serviceList' => 'required|array',
                // 'permissionStatus'=>'required
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $ulbId           = $req->ulbId;
            $serviceId         = $req->serviceList;


            collect($serviceId)->map(function ($item) use ($ulbId) {

                $mUlbModulePermission =  $this->_UlbServices;
                $checkExisting = $mUlbModulePermission::where('ulb_id', $ulbId)
                    ->where('service_id', $item['serviceId'])
                    ->first();

                if ($item['permissionStatus'] == 0)
                    $isSuspended = 0;

                if ($item['permissionStatus'] == 1)
                    $isSuspended = 1;

                if ($checkExisting) {

                    $req = new Request([
                        'id' => $checkExisting->id,
                        'ulbId' => $ulbId,
                        'serviceId' => $item['serviceId'],
                        'isSuspended' => $isSuspended,
                    ]);

                    $mUlbModulePermission->updateModuleUlb($req);
                } else {
                    $req = new Request([
                        'ulbId' => $ulbId,
                        'serviceId' => $item['serviceId'],
                        'isSuspended' => $isSuspended,
                    ]);
                    $mUlbModulePermission->addModuleUlb($req);
                }
            });

            // $checkExisting = WfWardUser::where('user_id', $req->userId)
            //     ->where('ward_id', $req->wardId)
            //     ->first();

            // if ($checkExisting)
            //     throw new Exception("User Exist");

            // $mWfWardUser = new WfWardUser();
            // $mWfWardUser->addWardUser($req);

            return responseMsg(true, "Successfully Saved", "");
        } catch (Exception $e) {
            return responseMsg(true,  $e->getMessage(), "");
        }
    }

    // get service lists with restpect to ulb id 

    public function ulbServicesList(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'ulbId'     => 'nullable|int',
                'moduleId'   =>  'required|int'
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $mUlbModulePermission = $this->_UlbModulePermission;
            $user = authUser();
            $ulbId = $user->ulb_id ?? $req->ulbId;
            $moduleId = $req->moduleId;
            $query = "select 
                            mm.id,
                            us.ulb_id,
                            mm.menu_string as services,
                            mm.route,
                            mom.module_name,
                            us.created_by,
                            case 
                                when us.service_id is null then false
                                else
                                    true  
                            end as permission_status
                        from menu_masters as mm
                        left join (select * from ulb_services where ulb_id=$ulbId and is_suspended = false) as us on us.service_id=mm.id
                        left join module_masters as mom on mom.id = mm.module_id,
                        where mom.id=$moduleId
                        order by us.id";

            $data = DB::select($query);
            return responseMsg(true, "Module List of Ulb", $data);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
    //
    public function ulbServicesListv1(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            ['ulbId'     => 'required|int',]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $mUlbModulePermission = $this->_UlbModulePermission;
            $ulbId = $req->ulbId;
            $moduleId = $req->moduleId;
            $query = "select 
                            sm.id,
                            smp.ulb_id,
                            sm.service_name as services,
                            sm.path,
                            mom.module_name,
                            case 
                                when smp.service_id is null then false
                                else
                                    true  
                            end as permission_status
                        from service_masters as sm   
                        left join (select * from service_mappings where ulb_id=$ulbId and status = 1) as smp on smp.service_id=sm.id
                        left join module_masters as mom on mom.id = sm.module_id
                        where mom.id=$moduleId
                        order by sm.id";

            $data = DB::select($query);
            return responseMsg(true, "Module List of Ulb", $data);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
    // create services
    public function createServiceMaster(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'serviceName'     => 'required|string',
                'path'            => 'required|string',
                'moduleId'        =>  'required|int'
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $mService   = new ServiceMaster();
            $data = $mService->store($req);
            return responseMsg(true, "Created Data Succesfully!", $data);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
    //  get service list 
    public  function getListService(Request $req)
    {
        try {
            $mService = new ServiceMaster();
            $data = $mService->getServices($req);
            return responseMsgs(true, "List Of Services", $data);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    //  get service list 
    public  function updateSeviceMater(Request $req)
    {
        try {
            $mService = new ServiceMaster();
            $data = $mService->updateMasters($req);
            return responseMsgs(true, "Updated Services", $data);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
    /**
     check module services of different ulb
     */
    public  function checkUlbModuleServices(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'moduleId'     => 'required|int',
                'path'            => 'required|string',
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $user = authUser();
            $ulbId = $user->ulb_id;
            $data = ServiceMapping::select(
                'service_masters.id'
            )
                ->join('service_masters', 'service_masters.id', 'service_mappings.service_id')
                ->where('service_mappings.ulb_id', $ulbId)
                ->where('service_masters.module_id', $req->moduleId)
                ->where('service_masters.path', $req->path)
                ->where('service_mappings.status', 1)
                ->first();
            if (!$data) {
                return responseMsgs(false, 'Service Resitricted!', "");
            }
            return responseMsgs(true, "Services Permission", $data);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
}
