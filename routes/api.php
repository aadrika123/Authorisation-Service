<?php

use App\Http\Controllers\ApiGatewayController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\CitizenController;
use App\Http\Controllers\Menu\MenuController;
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

    Route::controller(MenuController::class)->group(function () {
        Route::post('menu/by-module', 'getMenuByModuleId');                  // Get menu by role 

    });

    /**
     * | Created On-02-06-2023
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

    Route::controller(WorkflowMap::class)->group(function () {
        Route::post('workflow/getWardByUlb', 'getWardByUlb');
    });

    // Api Gateway Routes
    Route::controller(ApiGatewayController::class)->group(function () {
        Route::any('{any}', 'apiGatewayService')->where('any', '.*');
    });
});
