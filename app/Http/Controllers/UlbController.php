<?php

namespace App\Http\Controllers;

use App\MicroServices\DocUpload;
use App\Models\BlogPost;
use App\Models\Department;
use App\Models\DistrictMaster;
use App\Models\LogNewDepartment;
use App\Models\MCity;
use App\Models\ModuleMaster;
use App\Models\ServiceMapping;
use App\Models\ServiceMaster;
use App\Models\UlbMaster;
use App\Models\UlbModulePermission;
use App\Models\UlbNewWardmap;
use App\Models\UlbService;
use App\Models\UlbWardMaster;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class UlbController extends Controller
{
    protected $_UlbModulePermission;
    protected $_UlbServices;
    protected $_ulbMasters;
    protected $_department;
    protected $_logDepartment;
    public function __construct()
    {
        $this->_UlbModulePermission = new UlbModulePermission();
        $this->_UlbServices = new ServiceMapping();
        $this->_ulbMasters = new UlbMaster();
        $this->_department = new Department();
        $this->_logDepartment = new LogNewDepartment();
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
                            sm.id,
                            smp.ulb_id,
                            sm.service_name as services,
                            sm.path,
                            mom.module_name,
                            case 
                                when smp.status=0 then false
                                else
                                    true  
                            end as permission_status
                        from service_masters as sm   
                        left join module_masters as mom on mom.id = sm.module_id
                        left join service_mappings as smp on smp.service_id = sm.id
                        where mom.id=$moduleId
                        and smp.ulb_id=$ulbId
                        and smp.status = 1
                        order by sm.id";
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

    // Create ULB Permissions
    public function createUlbPermissions(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'ulbId' => 'required',
                'permissionStatus' => 'required|',
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $ulbId = $req->ulbId;
            $permissions = $req->permissionStatus;

            $mUlbMasters =  $this->_ulbMasters;

            $checkExisting = $mUlbMasters::where('id', $ulbId)->first();
            if ($checkExisting) {
                $req = new Request([
                    'id' => $checkExisting->id,
                    'ulbId' => $ulbId,
                    'permissions' => $permissions,
                ]);

                $mUlbMasters->updateUlbPermissions($req);
            } else {
                $req = new Request([
                    'ulbId' => $ulbId,
                    'permissions' => $permissions,
                ]);

                $mUlbMasters->addUlbMasterv1($req);
            }

            return responseMsg(true, "Successfully Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Upload ULB logo from ULB ID
     * | Path: public/upload/Icon
     */
    public function uploadUlbLogo(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'ulbId' => 'required|integer',
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $ulbId = $req->ulbId;
            $mUlbMasters = $this->_ulbMasters;

            $checkExisting = $mUlbMasters::where('id', $ulbId)->first();
            if (!$checkExisting) {
                throw new Exception('ULB not found');
            }

            // Delete previous logo if exists
            if ($checkExisting->logo && file_exists(public_path($checkExisting->logo))) {
                unlink(public_path($checkExisting->logo));
            }

            // Handle file upload
            if ($req->hasFile('logo')) {
                $file = $req->file('logo');
                $fileName = 'ulb_' . $ulbId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('Uploads/Icon'), $fileName);
                $logoPath = 'Uploads/Icon/' . $fileName;
            } else {
                throw new Exception('Logo file not found');
            }

            $updateReq = new Request([
                'id' => $checkExisting->id,
                'logoPath' => $logoPath,
            ]);
            $mUlbMasters->updateUlbLogo($updateReq);

            return responseMsg(true, "Logo uploaded successfully", ['logoPath' => $logoPath]);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Get all ULB logos with full URL
     */
    public function getAllUlbLogos()
    {
        try {
            $mUlbMasters = $this->_ulbMasters;
            $data = $mUlbMasters->getAllUlbLogos();
            return responseMsg(true, "ULB Logos retrieved successfully", $data);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    public function activeBlogsList(Request $req)
    {
        try {
            $blogModel = new BlogPost();
            $docUpload = new DocUpload();

            // Increment API hit count using Redis
            $today = now()->toDateString();

            $cacheKeyTotal = "dashboard_hits_total";
            $cacheKeyToday = "dashboard_hits_" . $today;

            $totalHits = Redis::incr($cacheKeyTotal);
            $todayHits = Redis::incr($cacheKeyToday);
            Redis::expire($cacheKeyToday, now()->endOfDay()->diffInSeconds());

            // Fetch blogs
            $blogs = $blogModel->getActiveBlogsList()->map(function ($val) use ($docUpload) {
                $url = $docUpload->getSingleDocUrl($val);
                $val->is_suspended = $val->status;
                $val->asset_file = $url["doc_path"] ?? null;
                return $val;
            });

            $response = [
                "status"   => true,
                "message"  => "All Blog List",
                "code"     => "BLOG002",
                "version"  => "01",
                "responseTime" => responseTime(),
                "method"   => $req->getMethod(),
                "deviceId" => $req->deviceId,
                "data"     => $blogs,   // 
                "hits"     => [         // 
                    "totalHits" => $totalHits,
                    "todayHits" => $todayHits
                ]
            ];


            return response()->json($response, 200);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "BLOG002", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    public function storeBlogPost(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'title' => 'required',
                'assetFile' => 'required|mimes:pdf,jpeg,png,jpg',
                'shortDescription' => 'required|nullable',
                'longDescription' => 'required|nullable',
                'officerName' => 'nullable',
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $req->merge(["document" => $req->assetFile]);
            $docUpload = new DocUpload();
            $data = $docUpload->checkDoc($req);

            if (!$data["status"]) {
                throw new Exception("Document not uploaded");
            }
            $req->merge($data["data"]);

            // Use model's custom store() method
            $create = new BlogPost();
            $stored = $create->store($req);

            if (!$stored) {
                throw new Exception("Blog post not stored");
            }

            return responseMsgs(true, "Blog created successfully", $stored, "BLOG001", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "BLOG001", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
    /* 
     * | get all blog post list
     */
    public function allBlogs(Request $req)
    {
        try {
            $blogModel = new BlogPost();
            $docUpload = new DocUpload();

            $data = $blogModel->allList()->map(function ($val) use ($docUpload) {
                $url = $docUpload->getSingleDocUrl($val);
                $val->is_suspended = $val->status;
                $val->asset_file = $url["doc_path"] ?? null;
                // unset($val->asset_file);
                return $val;
            });


            return responseMsgs(true, "All Blog List", $data, "BLOG002", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "BLOG002", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    /* 
     * | update blog 
     */
    public function editBlog(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                "id" => "required|numeric",
                "title" => "required|string",
                "asset_file" => "required|mimes:pdf,jpeg,png,jpg",
                "shortDescription" => "required|string",
                "longDescription" => "required|string",
                "officerName" => "required|string"
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $req->merge(["document" => $req->asset_file]);
            $docUpload = new DocUpload;
            $data = $docUpload->checkDoc($req);
            if (!$data["status"]) {
                throw new Exception("Document Not uploaded");
            }

            $req->merge($data["data"]);

            $blog = new BlogPost();
            if (!$blog->edit($req)) {
                throw new Exception("Data not updated");
            }

            return responseMsgs(true, "Blog updated", "", "BLOG003", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "BLOG003", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    /* 
     * | delete blog post
     */
    public function deleteBlog(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required',
                'status' => 'required'
            ]);

            $delete = new BlogPost();
            $message = $delete->deleteBlog($req);

            return responseMsgs(true, "", $message, "BLOG004", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "BLOG004", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    /* 
     * | get blog by id
     */
    public function blogById(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);

            $blog = new BlogPost();
            $message = $blog->getById($req);
            $docUpload = new DocUpload();
            $url = $docUpload->getSingleDocUrl($message);

            $message->is_suspended = $message->status;
            $message->blogFile = $url["doc_path"] ?? null;

            unset($message->document);
            return responseMsgs(true, "Blog Details", $message, "BLOG004", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "BLOG004", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Department Create
    public function departmentCreate(Request $req)
    {
        try {
            $validated = Validator::make($req->all(), [
                'department_name' => 'required|string|max:255',
                'description'     => 'nullable|string|max:255'
            ]);

            if ($validated->fails()) {
                return validationError($validated);
            }

            $dept = $this->_department->create($validated->validated());

            return responseMsgs(true, "Department created successfully", $dept, "DEPT001", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DEPT001", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // List all departments
    public function departmentList(Request $req)
    {
        try {
            $dept = $this->_department->where('status', 1)->orderBy('id', 'DESC')->get();

            return responseMsgs(true, "Departments fetched successfully", $dept, "DEPT002", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DEPT002", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Department details
    public function departmentDetail(Request $req)
    {
        try {
            $validated = Validator::make($req->all(), [
                'id' => 'required|exists:departments,id'
            ]);

            if ($validated->fails()) {
                 return responseMsgs(false,$validated->errors()->first(), "","DEPT003","01", responseTime(),"","");
            }

            $dept = $this->_department->where('id', $req->id)->where('status', 1)->first();
            
            if (!$dept) {
                return responseMsgs(false, "Department not found or inactive", "", "DEPT003", "01", responseTime(), $req->getMethod(), $req->deviceId);
            }

            return responseMsgs(true, "Department fetched successfully", $dept, "DEPT003", "01", responseTime(),$req->getMethod(), $req->deviceId);

        } catch (Exception $e) {

            return responseMsgs(false, $e->getMessage(), "","DEPT003", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Update Department
    public function updateDepartment(Request $req)
    {
        try {
            $validated = Validator::make($req->all(), [
                'id'              => 'required|exists:departments,id',
                'department_name' => 'required|string|max:255',
                'description'     => 'nullable|string|max:255'
            ]);

            if ($validated->fails()) {
                return validationError($validated);
            }

            $dept = $this->_department->find($req->id);
            $oldData = $dept->toArray();
            $dept->update($validated->validated());
            
            // Log the update
            $this->_logDepartment->create([
                'department_id' => $dept->id,
                'department_name' => $dept->department_name,
                'description' => $dept->description,
                'status' => $dept->status,
                'action' => 'UPDATE',
                'old_data' => $oldData,
                'new_data' => $dept->toArray()
            ]);

            return responseMsgs(true, "Department updated successfully", $dept, "DEPT004", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DEPT004", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Toggle Department Status
    public function toggleDepartmentStatus(Request $req)
    {
        try {
            $validated = Validator::make($req->all(), [
                'id' => 'required|exists:departments,id'
            ]);

            if ($validated->fails()) {
                return validationError($validated);
            }

            $dept = $this->_department->find($req->id);
            $newStatus = $dept->status == 1 ? 0 : 1;
            $dept->update(['status' => $newStatus]);
            
            $message = $newStatus == 1 ? "Department activated successfully" : "Department deactivated successfully";

            return responseMsgs(true, $message, "", "DEPT005", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DEPT005", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Delete Department
    public function deleteDepartment(Request $req)
    {
        try {
            $validated = Validator::make($req->all(), [
                'id' => 'required|exists:departments,id'
            ]);

            if ($validated->fails()) {
                return validationError($validated);
            }

            $dept = $this->_department->find($req->id);
            
            // Log before deletion
            $this->_logDepartment->create([
                'department_id' => $dept->id,
                'department_name' => $dept->department_name,
                'description' => $dept->description,
                'status' => $dept->status,
                'action' => 'DELETE',
                'old_data' => $dept->toArray(),
                'new_data' => null
            ]);
            
            $dept->delete();

            return responseMsgs(true, "Department deleted successfully", "", "DEPT006", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "DEPT006", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    public function moduleList(Request $request)
    {
        try {
            $mModuleMaster = new ModuleMaster();
            $data = $mModuleMaster->moduleListv2();

            return responseMsgs(true, "List of Module!", $data, "", "02", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "02", "", "POST", "");
        }
    }

    public function getWardByUlb(Request $request)
    {
        //validating
        $request->validate([
            'ulbId' => 'required|integer',
        ]);
        $ulbId = $request->ulbId;
        $wards = collect();
        $workkFlow = UlbWardMaster::select(
            'id',
            'ulb_id',
            'ward_name',
            'old_ward_name'
        )
            ->where('ulb_id', $ulbId)
            ->where('status', 1)
            ->orderby('id')
            ->get();

        $groupByWards = $workkFlow->groupBy('ward_name');
        foreach ($groupByWards as $ward) {
            $wards->push(collect($ward)->first());
        }
        $wards->sortBy('ward_name')->values();
        return responseMsg(true, "Data Retrived", remove_null($wards));
    }

    // Add Module
    public function addModule(Request $req)
    {
        try {
            $validated = Validator::make($req->all(), [
                'moduleName' => 'required|string|max:255',
                'title' => 'nullable|string|max:255',
                'url' => 'nullable|string|max:255',
                'image' => 'nullable|string|max:255',
                'duesApi' => 'nullable|string|max:255',
                'canView' => 'nullable|boolean'
            ]);

            if ($validated->fails()) {
                return validationError($validated);
            }

            $module = ModuleMaster::create([
                'module_name' => $req->moduleName,
                'title' => $req->title,
                'url' => $req->url,
                'image' => $req->image,
                'dues_api' => $req->duesApi,
                'can_view' => $req->canView ?? true,
                'is_suspended' => false
            ]);

            return responseMsgs(true, "Module created successfully", $module, "MOD001", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "MOD001", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Update Module
    public function updateModule(Request $req)
    {
        try {
            $validated = Validator::make($req->all(), [
                'id' => 'required|exists:module_masters,id',
                'moduleName' => 'required|string|max:255',
                'title' => 'nullable|string|max:255',
                'url' => 'nullable|string|max:255',
                'image' => 'nullable|string|max:255',
                'duesApi' => 'nullable|string|max:255',
                'canView' => 'nullable|boolean'
            ]);

            if ($validated->fails()) {
                return validationError($validated);
            }

            $module = ModuleMaster::findOrFail($req->id);
            $module->update([
                'module_name' => $req->moduleName,
                'title' => $req->title,
                'url' => $req->url,
                'image' => $req->image,
                'dues_api' => $req->duesApi,
                'can_view' => $req->canView ?? $module->can_view
            ]);

            return responseMsgs(true, "Module updated successfully", $module, "MOD002", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "MOD002", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    // Delete Module (Toggle is_suspended)
    public function deleteModule(Request $req)
    {
        try {
            $validated = Validator::make($req->all(), [
                'id' => 'required|exists:module_masters,id',
                'isSuspended' => 'required|boolean'
            ]);

            if ($validated->fails()) {
                return validationError($validated);
            }

            $module = ModuleMaster::findOrFail($req->id);
            $module->update(['is_suspended' => $req->isSuspended]);

            $message = $req->isSuspended ? "Module disabled successfully" : "Module enabled successfully";

            return responseMsgs(true, $message, "", "MOD003", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "MOD003", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }
}
