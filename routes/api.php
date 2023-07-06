<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ApiRoleController;
use App\Http\Controllers\ApiGatewayController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\CitizenController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\Menu\MenuRoleController;
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


// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    Route::controller(MenuController::class)->group(function () {
        Route::post('user-managment/v1/crud/menu/save', 'createMenu');
        Route::post('user-managment/v1/crud/menu/edit', 'updateMenu');
        Route::post('user-managment/v1/crud/menu/delete', 'deleteMenu');
        Route::post('user-managment/v1/crud/menu/get', 'getMenuById');
        Route::post('user-managment/v1/crud/menu/list', 'menuList');

        Route::post('menu-roles/update-menu-by-role', 'updateMenuByRole');
        Route::post('menu-roles/list-parent-serial', 'listParentSerial');
        Route::post('menu/get-menu-by-roles', 'getMenuByRoles');
        Route::post('menu/by-module', 'getMenuByModuleId');
        Route::post('sub-menu/get-children-node', 'getChildrenNode');
        Route::post('sub-menu/tree-structure', 'getTreeStructureMenu');
    });

    /**
     * | Menu Role CRUD Operation
     */
    Route::controller(MenuRoleController::class)->group(function () {
        Route::post('user-managment/v1/crud/menu-role/save', 'createMenuRole');
        Route::post('user-managment/v1/crud/menu-role/edit', 'updateMenuRole');
        Route::post('user-managment/v1/crud/menu-role/delete', 'deleteMenuRole');
        Route::post('user-managment/v1/crud/menu-role/get', 'getMenuRole');
        Route::post('user-managment/v1/crud/menu-role/list', 'listMenuRole');
    });

    /**
     * | API Role CRUD Operation
     */
    Route::controller(ApiRoleController::class)->group(function () {
        Route::post('user-managment/v1/crud/api-role/save', 'createApiRole');
        Route::post('user-managment/v1/crud/api-role/edit', 'updateApiRole');
        Route::post('user-managment/v1/crud/api-role/delete', 'deleteApiRole');
        Route::post('user-managment/v1/crud/api-role/get', 'getApiRole');
        Route::post('user-managment/v1/crud/api-role/list', 'listApiRole');
    });

    /**
     * | Workflow Role CRUD Operation
     */
    Route::controller(RoleController::class)->group(function () {
        Route::post('user-managment/v1/crud/workflow-role/save', 'createRole');                   // Save Role
        Route::post('user-managment/v1/crud/workflow-role/edit', 'editRole');                     // edit Role
        Route::post('user-managment/v1/crud/workflow-role/get', 'getRole');                       // Get Role By Id
        Route::post('user-managment/v1/crud/workflow-role/list', 'getAllRoles');                  // Get All Roles          
        Route::post('user-managment/v1/crud/workflow-role/delete', 'deleteRole');                 // Delete Role
    });

    /**
     * | Workflow Master CRUD operation
     */
    Route::controller(MasterController::class)->group(function () {
        Route::post('user-managment/v1/crud/workflow-master/save', 'createMaster');
        Route::post('user-managment/v1/crud/workflow-master/edit', 'updateMaster');
        Route::post('user-managment/v1/crud/workflow-master/get', 'masterbyId');
        Route::post('user-managment/v1/crud/workflow-master/list', 'getAllMaster');
        Route::post('user-managment/v1/crud/workflow-master/delete', 'deleteMaster');
    });

    /**
     * | Wf workflow CRUD operation
     */
    Route::controller(WorkflowController::class)->group(function () {
        Route::post('user-managment/v1/crud/wf-workflow/save', 'createWorkflow');                     // Save Workflow
        Route::post('user-managment/v1/crud/wf-workflow/edit', 'updateWorkflow');                     // Edit Workflow 
        Route::post('user-managment/v1/crud/wf-workflow/get', 'workflowbyId');                        // Get Workflow By Id
        Route::post('user-managment/v1/crud/wf-workflow/list', 'getAllWorkflow');                     // Get All Workflow
        Route::post('user-managment/v1/crud/wf-workflow/delete', 'deleteWorkflow');                   // Delete Workflow
    });

    /**
     * | Api master CRUD operation
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
     * | Workflow Role Map CRUD operation
     */
    Route::controller(WorkflowRoleMapController::class)->group(function () {
        Route::post('user-managment/v1/crud/workflow-role-map/save', 'createRoleMap');                     // Save Workflow
        Route::post('user-managment/v1/crud/workflow-role-map/edit', 'updateRoleMap');                     // Edit Workflow 
        Route::post('user-managment/v1/crud/workflow-role-map/get', 'roleMapbyId');                       // Get Workflow By Id
        Route::post('user-managment/v1/crud/workflow-role-map/list', 'getAllRoleMap');                     // Get All Workflow
        Route::post('user-managment/v1/crud/workflow-role-map/delete', 'deleteRoleMap');                   // Delete Workflow
        Route::post('user-managment/v1/crud/workflow-role-map/workflow-info', 'workflowInfo');
    });


    /**
     * | Ward User CRUD operation
     */
    Route::controller(WardUserController::class)->group(function () {
        Route::post('workflow/ward-user/save', 'createWardUser');                     // Save Workflow
        Route::post('workflow/ward-user/edit', 'updateWardUser');                     // Edit Workflow 
        Route::post('workflow/ward-user/get', 'WardUserbyId');                       // Get Workflow By Id
        Route::post('workflow/ward-user/list', 'getAllWardUser');                     // Get All Workflow
        Route::post('workflow/ward-user/delete', 'deleteWardUser');                   // Delete Workflow
        Route::post('workflow/ward-user/list-tc', 'tcList');
    });


    /**
     * | Role User Map CRUD operation
     */
    Route::controller(WorkflowRoleUserMapController::class)->group(function () {
        Route::post('workflow/role-user-maps/get-roles-by-id', 'getRolesByUserId');                        // Get Permitted Roles By User ID
        Route::post('workflow/role-user-maps/update-user-roles', 'updateUserRoles');                       // Enable or Disable User Role
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
        Route::post('workflow/getWardByUlb', 'getWardByUlb');
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
     * | 
     */
    Route::controller(UserController::class)->group(function () {
        Route::post('authorised-register', 'authorizeStore');               // authorised user adding user // 
        Route::post('change-password', 'changePass');                       // Change password with login
        Route::post('otp/change-password', 'changePasswordByOtp');           // Change Password With OTP   

        // User Profile APIs
        Route::get('my-profile-details', 'myProfileDetails');   // For get My profile Details
        Route::post('edit-my-profile', 'editMyProfile');        // For Edit My profile Details ---->>edited by mrinal method changed from put to post

        Route::post('edit-user', 'update');
        Route::post('delete-user', 'deleteUser');
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
