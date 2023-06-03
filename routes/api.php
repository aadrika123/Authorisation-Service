<?php

use App\Http\Controllers\ApiGatewayController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\CitizenController;
use App\Http\Controllers\WcController;
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



// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    /**
     * | Created On-02-06-2023
     * | Created By-Mrinal Kumar
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

    // Api Gateway Routes
    Route::controller(ApiGatewayController::class)->group(function () {
        Route::any('{any}', 'apiGatewayService')->where('any', '.*');
    });
});
