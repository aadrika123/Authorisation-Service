<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ApiRoleController;
use App\Http\Controllers\Api\ApiRoleMapController;
use App\Http\Controllers\Api\ApiRoleUserMapController;
use App\Http\Controllers\ApiGatewayController;
use App\Http\Controllers\ApiMasterController;
use App\Http\Controllers\ApiUnauthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\CitizenController;
use App\Http\Controllers\CustomController;
use App\Http\Controllers\EpramaanController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\Landingpage\LandingPageController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\Menu\MenuRoleController;
use App\Http\Controllers\Menu\MenuRoleMapController;
use App\Http\Controllers\Menu\MenuRoleUserMapController;
use App\Http\Controllers\Menu\TestController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ThirdPartyController;
use App\Http\Controllers\WorkflowMaster\MasterController;
use App\Http\Controllers\WorkflowMaster\RoleController;
use App\Http\Controllers\WorkflowMaster\WardUserController;
use App\Http\Controllers\WorkflowMaster\WorkflowController;
use App\Http\Controllers\WorkflowMaster\WorkflowRoleMapController;
use App\Http\Controllers\WorkflowMaster\WorkflowRoleUserMapController;
use App\Http\Controllers\UlbController;
use App\Http\Controllers\WcController;
use App\Http\Controllers\WorkflowMaster\WorkflowMap;
use App\Http\Controllers\WorkflowMaster\WorkflowMapController;
use App\Http\Controllers\ZoneMasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * | Landing Page API
 */
Route::controller(LandingPageController::class)->group(function () {
    Route::post('landing-page/list-scheme-type', 'getListSchemeType');
    Route::post('landing-page/add-scheme', 'addScheme');
    Route::post('landing-page/list-all-scheme', 'listAllScheme');
    Route::post('landing-page/delete-scheme', 'deleteScheme');
    Route::post('landing-page/edit-scheme', 'editScheme');
    Route::post('landing-page/list-type-wise-scheme', 'listTypeWiseScheme');
    Route::post('add-scheme-type', 'addSchemeType');                                               //march
    Route::post('update-scheme-type', 'updateSchemeType');                                         //march
    Route::post('get-scheme-type-by-id', 'getSchemeTypeById');                                     //march
    Route::post('scheme-type-delete', 'deleteShemeType');                                          //march
    Route::post('get-scheme-type', 'getSchemetype');                                          //march
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * | User Register & Login
 */
Route::controller(UserController::class)->group(function () {
    Route::post('login', 'loginAuth');                                                        #API_ID = 4161
    Route::post('register', 'store');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
    Route::post('get-citizen-dtl', 'citizenDtls')->middleware('auth:sanctum');
    Route::post('e_pramaanCheck', 'ePramanCheck');

    Route::post('user-managment/v1/crud/get/admin-list-v1', 'searchUsers');            #_List Admin by ulb Id
});

/**
 * | Citizen Register & Login
 */
Route::controller(CitizenController::class)->group(function () {
    Route::post('citizen-register', 'citizenRegister');
    Route::post('citizen-login', 'citizenLogin');
    Route::post('citizen-logout', 'citizenLogout')->middleware('auth:sanctum');
});

/**
 * | 
 */
Route::controller(UlbController::class)->group(function () {
    Route::get('get-all-ulb', 'getAllUlb');
    Route::post('v2/get-all-ulb', 'getAllUlbDtls');
    Route::post('create-ulb-master', 'createUlbmaster');
    Route::post('ulb-master-delete', 'deactiveUlbById');                                          //15/2024
    Route::post('get-ulb-by-id', 'getulbById');                                                    //15/2024
    Route::post('update-ulb-by-id', 'updateUlbId');                                                //15/2024
    Route::post('list-district', 'districtList');
    Route::post('city/state/ulb-id', 'getCityStateByUlb');
    Route::post('get-all-state', 'getAllState');
    Route::post('add-district', 'addDistrict');                                                //march
    Route::post('update-districtBy-Id', 'updateDistrict');                                     //march
    Route::post('get-districtBy-Id', 'getDistrictById');                                     //march
    Route::post('delete-district', 'deleteDistrict');                                         //march
    Route::post('get-district', 'getDistrictdtl');                                            //march
    Route::post('create-city', 'createCity');                                                 //march
    Route::post('get-city', 'getCity');                                                     //march
    Route::post('get-by-Id-city', 'getByIdCiTy');                                                     //march
    Route::post('city/enable-disable', 'enableOrDesable');                                   //march
    Route::post('update-city-by-id', 'updateCity');                                          //15/24
    #=====operation to make api for ulb wise module permissions ====#
    Route::post('create/ulb-wise-module', 'createModuleUlb');

    Route::post('get/moudle-by-Ulb-id', 'ulbModuleList');

    Route::post('remove/module-from-ulb', 'removeModuleFromUlb');
    #========== ulb wise services manage api ===============#

    Route::post('get/services-b-ulb-id-v1', 'ulbServicesListv1');

    Route::post('create/services', 'createServiceMaster');

    Route::post('get/service-list', 'getListService');

    Route::post('update/service-by-id', 'updateSeviceMater');

    Route::post('permissions/ulb-by-id', 'createUlbPermissions');
});

Route::controller(WorkflowMapController::class)->group(function () {
    Route::post('workflow/v2/crud/ward-by-ulb', 'getWardByUlb');        #_Ward Without Login
});

Route::controller(ThirdPartyController::class)->group(function () {
    Route::post('user/send-otp', 'sendOtp');
    Route::post('user/verify-otp', "verifyOtp");
    Route::post('forgot-password', 'forgotPasswordViaOtp');
    Route::post('otp-verification', 'otpVerification');
    Route::post('change-password-token', 'changePasswordViaToken');
    Route::post('change-password-dev', 'changesPasswordByDev');
});


/**
 * | E-Pramaan
 */
Route::controller(EpramaanController::class)->group(function () {
    Route::post('e-pramaan/login', 'loginEpramaan');
    Route::post('e-pramaan/dashboard', 'dashboardEpramaan');
    Route::post('e-pramaan/logout', 'logoutEpramaan');
    Route::post('e-pramaan/e-logout', 'eLogout');
});

/**
 * | Protected Routes
 * | Module Id = 12 
 * | Module Name = User Management
 */
Route::middleware('auth:sanctum')->group(function () {

    /**
     * | Api to Check if the User is authenticated or not
     */
    Route::post('/heartbeat', function () {                 // Heartbeat Api

        // return response()->json([
        //     'status' => true,
        //     'authenticated' => auth()->check()
        // ]);
    });

    /**
     * | Workflow Master CRUD operation
         Controller No : 01
     */
    Route::controller(MasterController::class)->group(function () {
        Route::post('user-managment/v1/crud/workflow-master/save', 'createMaster');    #API_ID=120101  | Save Workflow Master
        Route::post('user-managment/v1/crud/workflow-master/edit', 'updateMaster');    #API_ID=120102  | Edit Workflow Master 
        Route::post('user-managment/v1/crud/workflow-master/get', 'masterbyId');       #API_ID=120103  | Get Workflow Master By Id
        Route::post('user-managment/v1/crud/workflow-master/list', 'getAllMaster');    #API_ID=120104  | Get All Workflow Master
        Route::post('user-managment/v1/crud/workflow-master/delete', 'deleteMaster');  #API_ID=120105  | Delete Workflow Master
    });

    /**
     * | Wf workflow CRUD operation
          Controller No : 02
     */
    Route::controller(WorkflowController::class)->group(function () {
        Route::post('user-managment/v1/crud/wf-workflow/save', 'createWorkflow');      #API_ID=120201  | Save Workflow
        Route::post('user-managment/v1/crud/wf-workflow/edit', 'updateWorkflow');       #API_ID=120202  | Edit Workflow 
        Route::post('user-managment/v1/crud/wf-workflow/get', 'workflowbyId');         #API_ID=120203  | Get Workflow By Id
        Route::post('user-managment/v1/crud/wf-workflow/list', 'getAllWorkflow');      #API_ID=120204  | Get All Workflow
        Route::post('user-managment/v1/crud/wf-workflow/delete', 'deleteWorkflow');    #API_ID=120205  | Delete Workflow
    });

    /**
     * | Workflow Role CRUD Operation
         Controller No : 3
     */
    Route::controller(RoleController::class)->group(function () {
        Route::post('user-managment/v1/crud/workflow-role/save', 'createRole');        #API_ID=120301  | Save Workflow Role
        Route::post('user-managment/v1/crud/workflow-role/edit', 'editRole');          #API_ID=120302  | edit Workflow Role
        Route::post('user-managment/v1/crud/workflow-role/get', 'getRole');            #API_ID=120303  | Get Workflow Role By Id
        Route::post('user-managment/v1/crud/workflow-role/list', 'getAllRoles');       #API_ID=120304  | Get All Workflow Role          
        Route::post('user-managment/v1/crud/workflow-role/delete', 'deleteRole');      #API_ID=120305  | Delete Workflow Role
        Route::post('user-managment/v1/crud/workflow-role/selected-role', 'selectedRole');      #API_ID=120306  | Selected Workflow Role
        Route::post('user-managment/v1/crud/workflow-role/by-module', 'selectedRoleByModule');      #API_ID=120306  | Selected Workflow Role
    });

    /**
     * | Workflow Role Mapping CRUD operation
         Controller No : 4
     */
    Route::controller(WorkflowRoleMapController::class)->group(function () {
        Route::post('user-managment/v1/crud/workflow-role-map/save', 'createRoleMap');             // Save WorkflowRoleMap
        Route::post('user-managment/v1/crud/workflow-role-map/edit', 'updateRoleMap');             // Edit WorkflowRoleMap 
        Route::post('user-managment/v1/crud/workflow-role-map/get', 'roleMapbyId');                // Get WorkflowRoleMap By Id
        Route::post('user-managment/v1/crud/workflow-role-map/list', 'getAllRoleMap');             // Get All WorkflowRoleMap
        Route::post('user-managment/v1/crud/workflow-role-map/delete', 'deleteRoleMap');           // Delete WorkflowRoleMap
        Route::post('user-managment/v1/crud/workflow-role-map/workflow-info', 'workflowInfo');
        Route::post('workflow/role-map/workflow-info', 'workflowInfo');
    });

    /**
     * | Workflow Role User Mapping CRUD operation
         Controller No : 5
     */
    Route::controller(WorkflowRoleUserMapController::class)->group(function () {
        Route::post('user-managment/v1/crud/workflow-role-user/save', 'createRoleUser');                #API_ID=120501  |  #_Save WorkflowRoleUserMap
        Route::post('user-managment/v1/crud/workflow-role-user/edit', 'updateRoleUser');                #API_ID=120502  |  #_Edit WorkflowRoleUserMap 
        Route::post('user-managment/v1/crud/workflow-role-user/get', 'roleUserbyId');                   #API_ID=120503  |  #_Get WorkflowRoleUserMap By Id
        Route::post('user-managment/v1/crud/workflow-role-user/list', 'getAllRoleUser');                #API_ID=120504  |  #_Get All WorkflowRoleUserMap
        Route::post('user-managment/v1/crud/workflow-role-user/delete', 'deleteRoleUser');              #API_ID=120505  |  #_Delete WorkflowRoleUserMap
        Route::post('user-managment/v1/crud/workflow-role-user/by-user', 'roleByUserId');               #API_ID=120506  |  #_Get Permitted Roles By User ID
        Route::post('user-managment/v1/crud/workflow-role-user/excluding-user', 'roleExcludingUserId'); #API_ID=120507  |
        Route::post('workflow/role-user-maps/update-user-roles', 'updateUserRoles');                    #_Enable or Disable User Role
    });

    /**
     * | Menu Master CRUD operation
         Controller No : 6
     */
    Route::controller(MenuController::class)->group(function () {
        Route::post('user-managment/v1/crud/menu/save', 'createMenu');
        Route::post('user-managment/v1/crud/menu/edit', 'updateMenu');
        Route::post('user-managment/v1/crud/menu/delete', 'deleteMenu');
        Route::post('user-managment/v1/crud/menu/get', 'getMenuById');
        Route::post('user-managment/v1/crud/menu/list', 'menuList');
        Route::post('user-managment/v1/crud/module/list', 'moduleList')->withoutMiddleware('auth:sanctum');
        Route::post('user-managment/v2/crud/module/list', 'moduleListV2')->withoutMiddleware('auth:sanctum');
        Route::post('user-managment/v1/crud/menu/list-parent-serial', 'listParentSerial');

        Route::post('menu-roles/update-menu-by-role', 'updateMenuByRole');
        Route::post('menu/get-menu-by-roles', 'getMenuByRoles');
        Route::post('menu/by-module', 'getMenuByModuleId');
        Route::post('sub-menu/get-children-node', 'getChildrenNode');
        Route::post('sub-menu/tree-structure', 'getTreeStructureMenu');
    });

    /**
     * | Menu Role CRUD Operation
         Controller No : 7
     */
    Route::controller(MenuRoleController::class)->group(function () {
        Route::post('user-managment/v1/crud/menu-role/save', 'createMenuRole');
        Route::post('user-managment/v1/crud/menu-role/edit', 'updateMenuRole');
        Route::post('user-managment/v1/crud/menu-role/delete', 'deleteMenuRole');
        Route::post('user-managment/v1/crud/menu-role/get', 'getMenuRole');
        Route::post('user-managment/v1/crud/menu-role/list', 'listMenuRole');
        Route::post('user-managment/v1/crud/menu-role/menu-list', 'menuByMenuRole');
    });

    /**
     * | Menu Role Mapping CRUD operation
         Controller No : 8
     */
    Route::controller(MenuRoleMapController::class)->group(function () {
        Route::post('user-managment/v1/crud/menu-role-map/save', 'createRoleMap');                    // Save MenuRole
        Route::post('user-managment/v1/crud/menu-role-map/edit', 'updateRoleMap');                    // Edit MenuRole 
        Route::post('user-managment/v1/crud/menu-role-map/get', 'roleMapbyId');                       // Get MenuRole By Id
        Route::post('user-managment/v1/crud/menu-role-map/list', 'getAllRoleMap');                    // Get All MenuRole
        Route::post('user-managment/v1/crud/menu-role-map/delete', 'deleteRoleMap');                  // Delete MenuRole
    });

    /**
     * | Menu Role User Mapping CRUD operation
         Controller No : 9
     */
    Route::controller(MenuRoleUserMapController::class)->group(function () {
        Route::post('user-managment/v1/crud/menu-role-user/save', 'createRoleUser');              #API_ID=120501 | #_Save MenuRoleUser
        Route::post('user-managment/v1/crud/menu-role-user/edit', 'updateRoleUser');              #API_ID=120502 | #_Edit MenuRoleUser 
        Route::post('user-managment/v1/crud/menu-role-user/get', 'roleUserbyId');                 #API_ID=120503 | #_Get MenuRoleUser By Id
        Route::post('user-managment/v1/crud/menu-role-user/list', 'getAllRoleUser');              #API_ID=120504 | #_Get All MenuRoleUser
        Route::post('user-managment/v1/crud/menu-role-user/delete', 'deleteRoleUser');            #API_ID=120505 | #_Delete MenuRoleUser
        Route::post('user-managment/v1/crud/menu-role-user/by-user', 'roleByUserId');             #API_ID=120506 | #_Get MenuRoleUser By User Id
        Route::post('user-managment/v1/crud/menu-role-user/except-user', 'roleExcludingUserId');  #API_ID=120507 | #_Get MenuRoleUser Excluding User
    });


    /**
     * | Api master CRUD operation
         Controller No : 10
     */
    Route::controller(ApiController::class)->group(function () {
        Route::post('user-managment/v1/crud/api-master/save', 'createApi');                  // Save Api
        Route::post('user-managment/v1/crud/api-master/edit', 'updateApi');                  // Edit Api 
        Route::post('user-managment/v1/crud/api-master/get', 'apibyId');                     // Get Api By Id
        Route::post('user-managment/v1/crud/api-master/list', 'getAllApi');                  // Get All Api
        Route::post('user-managment/v1/crud/api-master/delete', 'deleteApi');                // Delete Api
        Route::post('user-managment/v1/developer-list', 'listDeveloper');                    // Developer List
        Route::post('user-managment/v1/api-category', 'listCategory');                       // Category List
    });

    /**
     * Api Permission Crud
     * created By Sandeep Bara
     * Date 08/08/2023
     * 
     */
    Route::controller(ApiMasterController::class)->group(function () {
        # menu api map api list
        Route::match(["get", "post"], 'row-api-list/{service?}/{sub_service?}', 'getRowApiList');
        Route::post('sav-menu-api-map', 'menuApiMapStore');
        Route::post('all-menu-api-map', 'menuApiMapList');
        Route::post('menu-api-map', 'menuApiMap');
        Route::post('edit-menu-api-map', 'menuApiMapUpdate');

        # user api Exclude api list
        Route::post('sav-user-api-exclude', 'userApiExcluldeStor');
        Route::post('all-user-api-exclude', 'userApiExcluldeList');
        Route::post('user-api-exclude', 'userApiExclulde');
        Route::post('edit-user-api-exclude', 'userApiExcluldeUpdate');

        # zone crud operation 
        Route::post('create-zone', 'createZone');
        Route::post('get-zone', 'getZone');
        Route::post('delete-zone', 'deleteZone');
        Route::post('update-zone', 'updateZone');                                                        //15/2024
        Route::post('get-zone-by-id', 'getZoneById');                                                        //15/2024
        Route::post('create-IdGeneration', 'createParam');                                                       // create Id Generation param
        Route::post('Id-generation-param-update', 'updateParam');                                                       // create Id Generation param

        # crud for ulb_ward_masters
        Route::post('create-ulb_ward_masters', 'createUlbWard');
        Route::post('get-all-ulb-ward', 'getAllUlbWard');
        Route::post('delete-ulb_ward_masters', 'deleteUlbWard');
        Route::post('update-ulb_ward_masters', 'updateUlbWard');
        Route::post('get-by-id-ulb_ward_masters', 'getById');
    });

    /**
     * | API Role CRUD Operation
         Controller No : 11
     */
    Route::controller(ApiRoleController::class)->group(function () {
        Route::post('user-managment/v1/crud/api-role/save', 'createApiRole');
        Route::post('user-managment/v1/crud/api-role/edit', 'updateApiRole');
        Route::post('user-managment/v1/crud/api-role/delete', 'deleteApiRole');
        Route::post('user-managment/v1/crud/api-role/get', 'getApiRole');
        Route::post('user-managment/v1/crud/api-role/list', 'listApiRole');
        Route::post('user-managment/v1/crud/api-role/api-list', 'apiByApiRole');
    });

    /**
     * | API Role Mapping CRUD operation
         Controller No : 12
     */
    Route::controller(ApiRoleMapController::class)->group(function () {
        Route::post('user-managment/v1/crud/api-role-map/save', 'createRoleMap');                    // Save APIRole
        Route::post('user-managment/v1/crud/api-role-map/edit', 'updateRoleMap');                    // Edit APIRole 
        Route::post('user-managment/v1/crud/api-role-map/get', 'roleMapbyId');                       // Get APIRole By Id
        Route::post('user-managment/v1/crud/api-role-map/list', 'getAllRoleMap');                    // Get All APIRole
        Route::post('user-managment/v1/crud/api-role-map/delete', 'deleteRoleMap');                  // Delete APIRole
    });

    /**
     * | API Role User Mapping CRUD operation
         Controller No : 13
     */
    Route::controller(ApiRoleUserMapController::class)->group(function () {
        Route::post('user-managment/v1/crud/api-role-user/save', 'createRoleUser');     #API_ID=121301 | #_Save ApiRoleUser
        Route::post('user-managment/v1/crud/api-role-user/edit', 'updateRoleUser');     #API_ID=121302 | #_Edit ApiRoleUser 
        Route::post('user-managment/v1/crud/api-role-user/get', 'roleUserbyId');        #API_ID=121303 | #_Get ApiRoleUser By Id
        Route::post('user-managment/v1/crud/api-role-user/list', 'getAllRoleUser');     #API_ID=121304 | #_Get All ApiRoleUser
        Route::post('user-managment/v1/crud/api-role-user/delete', 'deleteRoleUser');   #API_ID=121305 | #_Delete ApiRoleUser
        Route::post('user-managment/v1/crud/api-role-user/by-user', 'roleByUserId');    #API_ID=121306 | #_ApiRole By User Id
        Route::post('user-managment/v1/crud/api-role-user/except-user', 'roleExcludingUserId'); #API_ID=121307 | #_ApiRole Excluding User
    });


    /**
     * | Ward User CRUD operation
     */
    Route::controller(WardUserController::class)->group(function () {
        Route::post('user-managment/v1/crud/ward-user/save', 'createWardUser');       // Save WardUser
        Route::post('user-managment/v1/crud/ward-user/edit', 'updateWardUser');       // Edit WardUser 
        Route::post('user-managment/v1/crud/ward-user/get', 'WardUserbyId');         // Get WardUser By Id
        Route::post('user-managment/v1/crud/ward-user/list', 'getAllWardUser');       // Get All WardUser
        Route::post('user-managment/v1/crud/ward-user/delete', 'deleteWardUser');     // Delete WardUser
        Route::post('user-managment/v1/crud/ward-user/by-user', 'wardByUserId');     // Ward by user id
        Route::post('workflow/ward-user/list-tc', 'tcList');
        Route::post('workflow/get-all/list-tc', 'getTcTlJSKList');
    });


    /**
     * | Workflow Mapping
     */
    Route::controller(WorkflowMapController::class)->group(function () {

        //Mapping
        Route::post('workflow/getroledetails', 'getRoleDetails');
        Route::post('workflow/getUserById', 'getUserById');
        Route::post('workflow/getWorkflowNameByUlb', 'getWorkflowNameByUlb');
        Route::post('workflow/getRoleByUlb', 'getRoleByUlb');
        Route::post('workflow/getUserByRole', 'getUserByRole');     #both r same please use one
        Route::post('workflow/getUserByRoleId', 'getUserByRoleId'); #both r same please use one    
        Route::post('workflow/getRoleByWorkflow', 'getRoleByWorkflow');
        Route::post('workflow/getUserByWorkflow', 'getUserByWorkflow');
        Route::post('workflow/getWardsInWorkflow', 'getWardsInWorkflow');
        Route::post('workflow/getUlbInWorkflow', 'getUlbInWorkflow');
        Route::post('workflow/getWorkflowByRole', 'getWorkflowByRole');
        Route::post('workflow/getWardByRole', 'getWardByRole');

        Route::post('workflow/getUserInUlb', 'getUserInUlb');
        Route::post('workflow/getRoleInUlb', 'getRoleInUlb');
        Route::post('workflow/getWorkflowInUlb', 'getWorkflowInUlb');
        Route::post('workflow/getRoleByUserUlbId', 'getRoleByUserUlbId');
        Route::post('workflow/getRoleByWardUlbId', 'getRoleByWardUlbId');
        Route::post('workflow/get-ulb-workflow', 'getWorkflow');
        Route::post('user-managment/v1/crud/workflow-by-module', 'workflowbyModule');
        Route::post('workflow/v1/crud/ward-by-ulb', 'getWardByUlb');
    });

    /* | Created On-02-06-2023
     * | Created By-Mrinal Kumar
     */
    Route::controller(UlbController::class)->group(function () {
        Route::post('city/state/auth/ulb-id', 'getCityStateByUlb');
        Route::post('list-ulb-by-district', 'districtWiseUlb');
        Route::post('get-newward-by-oldward', 'getNewWardByOldWard');
        Route::post('v2/get-newward-by-oldward', 'getNewWardByOldWard')->withoutMiddleware('auth:sanctum');
        Route::post('create/ulb-wise-services', 'createServicesUlb');
        Route::post('get/services-b-ulb-id', 'ulbServicesList');
        Route::post('get/services-by-module', 'checkUlbModuleServices');
    });

    /**
     * | Workflow Traits
     */
    Route::controller(WcController::class)->group(function () {
        Route::post('workflow-current-user', 'workflowCurrentUser');
        Route::post('workflow-initiator', 'workflowInitiatorData');
        Route::post('role-by-user', 'roleIdByUserId');
        Route::post('ward-by-user', 'wardByUserId');
        Route::post('role-by-workflow', 'getRole');
        Route::post('initiator', 'initiatorId');
        Route::post('finisher', 'finisherId');
    });

    /**
     * | FAQ's
     */
    Route::controller(FaqController::class)->group(function () {
        Route::post('user-managment/v1/crud/faq/save', 'createfaq');                  // Save FAQ
        Route::post('user-managment/v1/crud/faq/edit', 'updatefaq');                  // Edit FAQ 
        Route::post('user-managment/v1/crud/faq/get', 'faqbyId')->withoutMiddleware('auth:sanctum');   // Get FAQ By Id
        Route::post('user-managment/v1/crud/faq/list', 'faqList')->withoutMiddleware('auth:sanctum');  // Get All FAQ
        Route::post('user-managment/v1/crud/faq/delete', 'deletefaq');                // Delete FAQ
    });

    /**
     * | 
     */
    Route::controller(UserController::class)->group(function () {
        Route::post('user-managment/v1/crud/user/create', 'createUser');              #_Authorised User Adding User
        Route::post('user-managment/v1/crud/user/update', 'updateUser');              #_For Edit/Update User Details
        Route::post('user-managment/v1/crud/user/delete', 'deleteUser');              #_Delete User
        Route::post('user-managment/v1/crud/user/list', 'listUser');                  #_List User
        Route::post('user-managment/v1/crud/user/get', 'userById');                   #_Get User
        Route::post('user-managment/v1/crud/multiple-user/list', 'multipleUserList'); #_Get Multiple User
        Route::post('user-managment/v1/crud/get/user-type', 'listUserType');          #_List User Type
        Route::post('user-managment/v1/crud/get/admin-list', 'listAdmin');            #_List Admin
        Route::post('user-managment/v1/crud/get/user-list-by-ulb-id', 'listUserByUlbId');            #_List Admin


        Route::post('change-password', 'changePass');                       // Change password with login
        Route::post('otp/change-password', 'changePasswordByOtp');           // Change Password With OTP   

        // User Profile APIs
        Route::get('my-profile-details', 'myProfileDetails');   // For get My profile Details

        Route::get('get-user/{id}', 'getUser');
        Route::post('get-all-users', 'getAllUsers');
        Route::post('list-employees', 'employeeList');
        Route::post('get-user-notifications', 'userNotification');
        Route::post('add-user-notification', 'addNotification');
        Route::post('delete-user-notification', 'deactivateNotification');
        Route::post('hash-password', 'hashPassword');
        Route::post('user/password-reset', 'resetPassword');
    });

    // Citizen Register
    Route::controller(CitizenController::class)->group(function () {
        Route::get('get-citizen-by-id/{id}', 'getCitizenByID');                                                // Get Citizen By ID
        Route::get('get-all-citizens', 'getAllCitizens');                                                      // Get All Citizens
        Route::post('edit-citizen-profile', 'citizenEditProfile');                                             // Approve Or Reject Citizen by Id
        Route::post('change-citizen-pass', 'changeCitizenPass');                                               // Change the Password of The Citizen Using its Old Password 
        Route::post('otp/change-citizen-pass', 'changeCitizenPassByOtp');                                      // Change Password using OTP for Citizen
        Route::post('citizen-profile-details', 'profileDetails');
    });

    Route::controller(PermissionController::class)->group(function () {
        Route::post('permissions/get-user-permission', 'getUserPermission');                        // 01
    });

    /**
     * | for custom details
     */
    Route::controller(CustomController::class)->group(function () {
        Route::post('get-all-custom-tab-data', 'getCustomDetails');
        Route::post('post-custom-data', 'postCustomDetails');
        Route::post('get-dues-api', 'duesApi');
        Route::post('post-geo-location', 'tcGeoLocation');
        Route::post('list-location', 'locationList');
        Route::post('tc-collection-route', 'tcCollectionRoute');
        Route::post('list-quick-access', 'quickAccessList');
        Route::post('quick-access-byuserid', 'getQuickAccessListByUser');
        Route::post('add-update-quickaccess', 'addUpdateQuickAccess');
    });

    Route::controller(WorkflowMap::class)->group(function () {
        Route::post('workflow/getWardByUlb', 'getWardByUlb');
    });
});
// Api Gateway Routes for Unauth middleware required= 'apiPermission',
Route::middleware(['apiPermission'])->group(function () {
    Route::controller(ApiUnauthController::class)->group(function () {
        Route::get('trade/payment-receipt/{id}/{transectionId}', 'unAuthApis');
        Route::get('trade/provisional-certificate/{id}', 'unAuthApis');
        Route::get('trade/license-certificate/{id}', 'unAuthApis');
        Route::post("public-transport/agent/login", "unAuthApis");
        Route::post("public-transport/agent/logout", "unAuthApis");
        Route::get("public-transport/ticket/verify/{id}", "unAuthApis");


        # Grievance UnAuth Api
        Route::post("grievance/auth/req-otp", "unAuthApis");
        Route::post("grievance/auth/verify-otp", "unAuthApis");
        // Route::post("grievance/register-grievance", "unAuthApis");
        Route::post("grievance/auth/get-grievance", "unAuthApis");

        # Pet registration
        Route::post("pet/get-master-data", "unAuthApis");
        Route::post("pet/application/license", "unAuthApis");
        Route::post("pet/application/payment-receipt", "unAuthApis");

        # Water Tanker UnAuth Api
        Route::match(["get", 'post'], "water-tanker/get-water-tanker-reciept/{tranId}", "unAuthApis");
        # Septic Tank UnAuth Api
        Route::match(["get", 'post'], "septic-tanker/get-septic-tanker-reciept/{tranId}", "unAuthApis");
        # Advertisement UnAuth Api
        Route::match(["get", 'post'], "advert/get-payment-reciept/{tranId}/{workflowId}", "unAuthApis");
        # Juidco Dashboard UnAuth Api
        Route::post("property/reports/mpl", "unAuthApis");
        Route::post("property/'report/ulb-list", "unAuthApis");
        Route::post("property/reports/mpl2", "unAuthApis");
        Route::post("property/map/level1", "unAuthApis");
        Route::post("property/map/level2", "unAuthApis");
        # Property UnAuth Api
        Route::match(['post'], "property/saf/master-saf", "unAuthApis");
        Route::match(["get", 'post'], "property/calculatePropertyTax", "unAuthApis");
        Route::match(["get", 'post'], "property/search-holding", "unAuthApis");
        Route::post('property/location', 'unAuthApis');
        Route::post('property/report/mpl-todayCollection-new', 'unAuthApis');
        Route::post('property/location/ward-list', 'unAuthApis');
        Route::post('property/saf/payment-receipt', 'unAuthApis');
        Route::post('property/grievance/get-filter-property-details', 'unAuthApis');
        Route::post('payment/razorpay-webhook', 'unAuthApis');
        Route::post('property/prop-payment-receipt', 'unAuthApis');



        // Route::match(["get", 'post'], "property/independent/get-holding-dues", "unAuthApis");
        Route::post("property/independent/get-holding-dues", "unAuthApis");
        Route::post("property/independent/generate-prop-orderid", "unAuthApis");
        Route::post("property/saf/independent/generate-order-id", "unAuthApis");
        Route::post("property/prop-payment-receipt", "unAuthApis");
        Route::post("property/saf/list-apartment", "unAuthApis");
        Route::post("property/m-heading-list-master", "unAuthApis");
        Route::post("property/m-heading-list-master-desc", "unAuthApis");
        Route::post("property/m-dashboard-data", "unAuthApis");
        Route::post("property/reports/oldHolding", "unAuthApis");

        Route::post("property/m-dashboard-slider-data", "unAuthApis");


        #_Marriage
        Route::post("marriage/save-tran-dtl", "unAuthApis");
        Route::post("marriage/payment-receipt", "unAuthApis");

        #_Fines
        Route::post("fines/penalty-record/payment-receipt", "unAuthApis");
        Route::post("fines/user/enf-officer", "unAuthApis");
        Route::post('fines/deactivate-application', "unAuthApis");
        Route::post('fines/deactivate-challan', "unAuthApis");
        Route::post('fines/deactivate-payment', "unAuthApis");
        Route::post('fines/citizen-online-payment', "unAuthApis");
        Route::post('fines/penalty-record/get-challan', "unAuthApis");
        Route::post('fines/penalty-record/payment-receipt', "unAuthApis");
        Route::post('fines/penalty-record/citizen-challan-search', "unAuthApis");
        Route::post('fines/penalty-record/get-tran-no', "unAuthApis");
        Route::post('fines/v2/violation/crud/list', "unAuthApis");
        Route::post('fines/razorpay/save-response', "unAuthApis");
        Route::post('fines/mini-dashboard', "unAuthApis");
        Route::post('fines/top-collection', "unAuthApis");
        Route::post('fines/today-ulb-collection', "unAuthApis");

        #_Rig
        Route::post("rig/application/payment-receipt", "unAuthApis");
        Route::post("rig/get-approve-registration-list-V1", "unAuthApis");

        #_Advert
        Route::post('advert/get-approval-letter', 'unAuthApis');                                         // 02 ( Get All Approval Letter )



        #_Payment
        Route::post("payment/verify-payment-status", "unAuthApis");
        Route::post("payment/get-tran-by-orderid", "unAuthApis");
        //ptms
        Route::match(["get", 'post'], "ptms/v1/prime-dashboard", "unAuthApis");

        // swm
        Route::post("swm/getReprintData-v2", "unAuthApis");
    });
});

# Autherisation middleware required= 'apiPermission',
Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(ApiGatewayController::class)->group(function () {
        Route::any('{any}', 'apiGatewayService')->where('any', '.*');
    });
});
