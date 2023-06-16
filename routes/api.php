<?php

use App\Http\Controllers\ApiGatewayController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\CitizenController;
use App\Http\Controllers\Menu\MenuController;
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

    Route::controller(MenuController::class)->group(function(){
        Route::post('crud/menu/get-all-menues','getAllMenues');
        Route::post('crud/menu/get-menu-by-roles','getMenuByRoles');
        Route::post('menu-roles/update-menu-by-role','updateMenuByRole');
        Route::post('crud/menu/add-new-menues','addNewMenues');
        Route::post('crud/menu/delete-menues','deleteMenuesDetails');
        Route::post('sub-menu/tree-structure', 'getTreeStructureMenu');  
        Route::post('menu-roles/list-parent-serial', 'listParentSerial');  
        Route::post('sub-menu/get-children-node', 'getChildrenNode'); 
        Route::post('crud/menu/update-menues', 'updateMenuMaster');  
        Route::post('menu/get-menu-by-id', 'getMenuById'); 
        Route::post('menu/get-menu-by-module-id', 'getMenuByModuleId'); 
    });

    /**
     * workflow Master CRUD operation
     */
    Route::controller(MasterController::class)->group(function(){
        Route::post('workflow/master/save', 'createMaster');
        Route::post('workflow/master/edit', 'updateMaster');
        Route::post('workflow/master/byId', 'masterbyId');  
        Route::post('workflow/master/list', 'getAllMaster'); 
        Route::post('workflow/master/delete', 'deleteMaster'); 
                     // Get menu by role 

    });


    /**
     * Wf workflow CRUD operation
     */

    Route::controller(WorkflowController::class)->group(function () {
        Route::post('wfworkflow/save', 'createWorkflow');                     // Save Workflow
        Route::post('wfworkflow/edit', 'updateWorkflow');                     // Edit Workflow 
        Route::post('wfworkflow/byId', 'workflowbyId');                       // Get Workflow By Id
        Route::post('wfworkflow/list', 'getAllWorkflow');                     // Get All Workflow
        Route::post('wfworkflow/delete', 'deleteWorkflow');                   // Delete Workflow
    });


    /**
     * ============== Role Controller CRUD Operation =================
     */
    Route::controller(RoleController::class)->group(function () {
        Route::post('roles/save', 'createRole');                   // Save Role
        Route::post('roles/edit', 'editRole');                     // edit Role
        Route::post('roles/get', 'getRole');                       // Get Role By Id
        Route::post('roles/list', 'getAllRoles');                  //Get All Roles          
        Route::post('roles/delete', 'deleteRole');                 // Delete Role
    });
    /**
     * ===================================================================
     */

     
    /**
     * Ward User CRUD operation
     */
    Route::controller(WardUserController::class)->group(function () {
        Route::post('ward-user/save', 'createWardUser');                     // Save Workflow
        Route::post('ward-user/edit', 'updateWardUser');                     // Edit Workflow 
        Route::post('ward-user/byId', 'WardUserbyId');                       // Get Workflow By Id
        Route::post('ward-user/list', 'getAllWardUser');                     // Get All Workflow
        Route::post('ward-user/delete', 'deleteWardUser');                   // Delete Workflow
        Route::post('ward-user/list-tc', 'tcList');
    });


    // /**
    //  * Role User Map CRUD operation
    //  */
    Route::controller(WorkflowRoleUserMapController::class)->group(function () {
        Route::post('role-user-maps/get-roles-by-id', 'getRolesByUserId');                        // Get Permitted Roles By User ID
        Route::post('role-user-maps/update-user-roles', 'updateUserRoles');                       // Enable or Disable User Role
    });

    /**
     * Workflow Role Map CRUD operation
     */
    Route::controller(WorkflowRoleMapController::class)->group(function () {
        Route::post('role-map/save', 'createRoleMap');                     // Save Workflow
        Route::post('role-map/edit', 'updateRoleMap');                     // Edit Workflow 
        Route::post('role-map/byId', 'roleMapbyId');                       // Get Workflow By Id
        Route::post('role-map/list', 'getAllRoleMap');                     // Get All Workflow
        Route::post('role-map/delete', 'deleteRoleMap');                   // Delete Workflow
        Route::post('role-map/workflow-info', 'workflowInfo');
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
        Route::get('get-all-users', 'getAllUsers');
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

    /**
     * Workflow Mapping CRUD operation
     */

     Route::controller(WorkflowMa::class)->group(function () {

        //Mapping
        Route::post('getroledetails', 'getRoleDetails');
        Route::post('getUserById', 'getUserById');
        Route::post('getWorkflowNameByUlb', 'getWorkflowNameByUlb');
        Route::post('getRoleByUlb', 'getRoleByUlb');
        Route::post('getWardByUlb', 'getWardByUlb');
        Route::post('getUserByRole', 'getUserByRole');

        //mapping
        Route::post('getRoleByWorkflow', 'getRoleByWorkflow');
        Route::post('getUserByWorkflow', 'getUserByWorkflow');
        Route::post('getWardsInWorkflow', 'getWardsInWorkflow');
        Route::post('getUlbInWorkflow', 'getUlbInWorkflow'); 
        Route::post('getWorkflowByRole', 'getWorkflowByRole');
        Route::post('getUserByRoleId', 'getUserByRoleId');
        Route::post('getWardByRole', 'getWardByRole');
        Route::post('getUlbByRole', 'getUlbByRole');
        Route::post('getUserInUlb', 'getUserInUlb');
        Route::post('getRoleInUlb', 'getRoleInUlb');
        Route::post('getWorkflowInUlb', 'getWorkflowInUlb');

        Route::post('getRoleByUserUlbId', 'getRoleByUserUlbId');
        Route::post('getRoleByWardUlbId', 'getRoleByWardUlbId');

        Route::post('get-ulb-workflow', 'getWorkflow');
    });



    Route::controller(TestController::class)->group(function(){
        Route::post('repo/test','test');
    });
    
    Route::controller(WorkflowMap::class)->group(function () {
        Route::post('workflow/getWardByUlb', 'getWardByUlb');
    });

    // Api Gateway Routes
    Route::controller(ApiGatewayController::class)->group(function () {
        Route::any('{any}', 'apiGatewayService')->where('any', '.*');
    });

});


    