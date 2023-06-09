<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ApiRoleController;
use App\Http\Controllers\Api\ApiRoleMapController;
use App\Http\Controllers\Api\ApiRoleUserMapController;
use App\Http\Controllers\ApiGatewayController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\CitizenController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\Menu\MenuRoleController;
use App\Http\Controllers\Menu\MenuRoleMapController;
use App\Http\Controllers\Menu\MenuRoleUserMapController;
use App\Http\Controllers\Menu\TestController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * | User Register & Login
 */
Route::controller(UserController::class)->group(function () {
    Route::post('login', 'loginAuth');
    Route::post('register', 'store');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
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
    Route::post('city/state/ulb-id', 'getCityStateByUlb');
});


/**
 * | Protected Routes
 * | Module Id = 12 
 * | Module Name = User Management
 */
Route::middleware('auth:sanctum')->group(function () {

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
        Route::post('user-managment/v1/crud/wf-workflow/edit', 'updateWorkflow');      #API_ID=120202  | Edit Workflow 
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
    });

    /**
     * | Workflow Role User Mapping CRUD operation
         Controller No : 5
     */
    Route::controller(WorkflowRoleUserMapController::class)->group(function () {
        Route::post('user-managment/v1/crud/workflow-role-user/save', 'createRoleUser');                // Save WorkflowRoleUserMap
        Route::post('user-managment/v1/crud/workflow-role-user/edit', 'updateRoleUser');                // Edit WorkflowRoleUserMap 
        Route::post('user-managment/v1/crud/workflow-role-user/get', 'roleUserbyId');                   // Get WorkflowRoleUserMap By Id
        Route::post('user-managment/v1/crud/workflow-role-user/list', 'getAllRoleUser');                // Get All WorkflowRoleUserMap
        Route::post('user-managment/v1/crud/workflow-role-user/delete', 'deleteRoleUser');              // Delete WorkflowRoleUserMap
        Route::post('workflow/role-user-maps/get-roles-by-id', 'getRolesByUserId');                     // Get Permitted Roles By User ID
        Route::post('workflow/role-user-maps/update-user-roles', 'updateUserRoles');                    // Enable or Disable User Role
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
        Route::post('user-managment/v1/crud/module/list', 'moduleList');
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
        Route::post('user-managment/v1/crud/menu-role-user/save', 'createRoleUser');                  // Save MenuRoleUser
        Route::post('user-managment/v1/crud/menu-role-user/edit', 'updateRoleUser');                  // Edit MenuRoleUser 
        Route::post('user-managment/v1/crud/menu-role-user/get', 'roleUserbyId');                     // Get MenuRoleUser By Id
        Route::post('user-managment/v1/crud/menu-role-user/list', 'getAllRoleUser');                  // Get All MenuRoleUser
        Route::post('user-managment/v1/crud/menu-role-user/delete', 'deleteRoleUser');                // Delete MenuRoleUser
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
        Route::post('user-managment/v1/crud/api-role-user/save', 'createRoleUser');                  // Save ApiRoleUser
        Route::post('user-managment/v1/crud/api-role-user/edit', 'updateRoleUser');                  // Edit ApiRoleUser 
        Route::post('user-managment/v1/crud/api-role-user/get', 'roleUserbyId');                     // Get ApiRoleUser By Id
        Route::post('user-managment/v1/crud/api-role-user/list', 'getAllRoleUser');                  // Get All ApiRoleUser
        Route::post('user-managment/v1/crud/api-role-user/delete', 'deleteRoleUser');                // Delete ApiRoleUser
    });


    /**
     * | Ward User CRUD operation
     */
    Route::controller(WardUserController::class)->group(function () {
        Route::post('workflow/ward-user/save', 'createWardUser');                     // Save WardUser
        Route::post('workflow/ward-user/edit', 'updateWardUser');                     // Edit WardUser 
        Route::post('workflow/ward-user/get', 'WardUserbyId');                       // Get WardUser By Id
        Route::post('workflow/ward-user/list', 'getAllWardUser');                     // Get All WardUser
        Route::post('workflow/ward-user/delete', 'deleteWardUser');                   // Delete WardUser
        Route::post('workflow/ward-user/list-tc', 'tcList');
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
        Route::post('list-district', 'districtList');
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
        Route::post('user-managment/v1/crud/faq/get', 'faqbyId');                     // Get FAQ By Id
        Route::post('user-managment/v1/crud/faq/list', 'faqList');                    // Get All FAQ
        Route::post('user-managment/v1/crud/faq/delete', 'deletefaq');                // Delete FAQ

    });

    /**
     * | 
     */
    Route::controller(UserController::class)->group(function () {
        Route::post('user-managment/v1/crud/user/create', 'createUser');    #_Authorised User Adding User
        Route::post('user-managment/v1/crud/user/update', 'updateUser');    #_For Edit/Update User Details
        Route::post('user-managment/v1/crud/user/delete', 'deleteUser');    #_Delete User
        Route::post('user-managment/v1/crud/user/list', 'listUser');        #_List User
        Route::post('user-managment/v1/crud/user/get', 'userById');         #_Get User


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

    Route::controller(TestController::class)->group(function () {
        Route::post('repo/test', 'test');
    });

    Route::controller(WorkflowMap::class)->group(function () {
        Route::post('workflow/getWardByUlb', 'getWardByUlb');
    });

    // Api Gateway Routes
    Route::controller(ApiGatewayController::class)->group(function () {
        Route::any('{any}', 'apiGatewayService')->where('any', '.*');
    });
});
