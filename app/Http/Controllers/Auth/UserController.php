<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthorizeRequestUser;
use App\Http\Requests\Auth\AuthUserRequest;
use App\Http\Requests\Auth\ChangePassRequest;
use App\Http\Requests\Auth\OtpChangePass;
use App\Models\Auth\User;
use App\Models\EPramanExistCheck;
use App\Models\ModuleMaster;
use App\Models\Notification\MirrorUserNotification;
use App\Models\Notification\UserNotification;
use App\Models\UlbMaster;
use App\Models\UlbWardMaster;
use App\Models\Workflows\WfRole;
use App\Models\Workflows\WfRoleusermap;
use App\Pipelines\User\SearchByEmail;
use App\Pipelines\User\SearchByMobile;
use App\Pipelines\User\SearchByName;
use App\Pipelines\User\SearchByRole;
use App\Traits\Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use function PHPUnit\Framework\throwException;

class UserController extends Controller
{
    use Auth;
    private $_mUser;
    private $_MenuMobileMaster;
    private $_UserMenuMobileExclude;
    private $_UserMenuMobileInclude;
    private $_ModuleMaster;
    public function __construct()
    {
        $this->_mUser = new User();
        $this->_ModuleMaster = new ModuleMaster();
        // $this->_MenuMobileMaster = new MenuMobileMaster();
        // $this->_UserMenuMobileExclude   = new UserMenuMobileExclude();
        // $this->_UserMenuMobileInclude   = new UserMenuMobileInclude();
    }

    /**
     * | User Login
     */
    public function loginAuth(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
                'type' => "nullable|in:mobile"
            ]
        );
        if ($validated->fails())
            return validationError($validated);
        try {
            $mWfRoleusermap = new WfRoleusermap();
            $user = $this->_mUser->getUserByEmail($req->email);
            if (!$user)
                throw new Exception("Invalid Credentials");
            if ($user->suspended == true)
                throw new Exception("You are not authorized to log in!");
            if (Hash::check($req->password, $user->password)) {
                $token = $user->createToken('my-app-token')->plainTextToken;
                $menuRoleDetails = $mWfRoleusermap->getRoleDetailsByUserId($user->id);
                // if (empty(collect($menuRoleDetails)->first())) {
                //     throw new Exception('User has No Roles!');
                // }
                $role = collect($menuRoleDetails)->map(function ($value, $key) {
                    $values = $value['roles'];
                    return $values;
                });
                $roleId = collect($menuRoleDetails)->map(function ($value, $key) {
                    $values = $value['roleId'];
                    return $values;
                });
                if (!$req->type && $this->checkMobileUserRole($menuRoleDetails)) {
                    throw new Exception("Mobile user not login as web user");
                }
                $jeRole = collect($menuRoleDetails)->where('roles', 'JUNIOR ENGINEER');

                if (collect($jeRole)->isEmpty()) {
                    if ($req->type && !$this->checkMobileUserRole($menuRoleDetails)) {
                        throw new Exception("Web user not login as mobile user");
                    }
                }
                $user->ulbName = UlbMaster::find($user->ulb_id)->ulb_name ?? "";
                $data['token'] = $token;
                $data['userDetails'] = $user;
                $data['userDetails']['role'] = $role;
                $data['userDetails']['roleId'] = $roleId;
                return responseMsgs(true, "You have Logged In Successfully", $data, 010101, "1.0", responseTime(), "POST", $req->deviceId);
            }

            throw new Exception("Invalid Credentials");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    private function checkMobileUserRole($menuRoleDetails)
    {

        foreach ($menuRoleDetails as $role) {
            if (in_array($role->roles, ['TAX COLLECTOR', 'ULB TAX COLLECTOR', 'TAX DAROGA', 'DRIVER', 'SEPTIC TANKER DRIVER', 'ENFORCEMENT OFFICER', "CONDUCTOR", "PARKING INCHARGE", "LAMS FieldOfficer"])) {

                return true;
            }
        }
        return false;
    }

    /**
     * | logout
     */
    public function logout(Request $req)
    {
        try {
            $req->user()->currentAccessToken()->delete();                               // Delete the Current Accessable Token
            return responseMsgs(true, "You have Logged Out", [], "", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return response()->json($e, 400);
        }
    }

    /**
     * | User Creation
     */
    public function createUser(AuthUserRequest $request)
    {
        try {
            // Validation---@source-App\Http\Requests\AuthUserRequest
            $user = new User;
            $checkEmail = User::where('email', $request->email)->first();
            if ($checkEmail)
                throw new Exception('The email has already been taken.');
            $this->saving($user, $request);                     #_Storing data using Auth trait
            $firstname = explode(" ", $request->name);
            $user->user_name = $firstname[0] . '.' . substr($request->mobile, 0, 3);
            $user->password = Hash::make($firstname[0] . '@' . substr($request->mobile, 7, 3));

            DB::beginTransaction();
            $user->save();
            if ($request->role == 'ADMIN') {
                $this->assignRole($request->role, $user);
            }
            DB::commit();
            $data['userName'] = $user->user_name;
            return responseMsgs(true, "User Registered Successfully !! Please Continue to Login.
            Your Password is Your first name @ Your last 3 digit of your Mobile No", $data);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Role addition of ther user
     */
    public function assignRole($role, $user)
    {
        $adminRole = Config::get('constants.ADMIN_ROLE');
        $loggedInUser = authUser()->id;
        $mWfRoleUser = new WfRoleusermap();
        $mWfRoleusermap = new WfRoleusermap();
        $roleIds = $mWfRoleUser->getRoleIdByUserId($loggedInUser)->pluck('wf_role_id')->toArray();                      // Model to () get Role By User Id
        if (in_array($adminRole, $roleIds)) {
            $roleList = WfRole::get();
            $roleId = collect($roleList)->where('role_name', $role)->first()->id;
            $addRequest = new Request([
                "wfRoleId" => $roleId,
                "userId" => $user->id,
                "createdBy" => $loggedInUser,
            ]);
            $mWfRoleusermap->addRoleUser($addRequest);
        } else
            throw new Exception("You r not Authorized");
    }


    /**
     * | Update User Details
     */
    public function updateUser(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                "id" => 'required'
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $id = $request->id;
            $user = User::find($id);
            if (!$user)
                throw new Exception("User Not Exist");
            $stmt = $user->email == $request->email;
            if ($stmt) {
                $this->saving($user, $request);
                $this->savingExtras($user, $request);
                $user->save();
            }
            if (!$stmt) {
                $check = User::where('email', $request->email)->first();
                if ($check) {
                    throw new Exception('Email Is Already Existing');
                }
                if (!$check) {
                    $this->saving($user, $request);
                    $this->savingExtras($user, $request);
                    $user->save();
                }
            }
            return responseMsgs(true, "Successfully Updated", "", "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    /**
     * | List User
     */
    public function listUser(Request $req)
    {
        try {
            $perPage = $req->perPage ?? 10;
            $ulbId = $req->ulbId ?? authUser()->ulb_id;
            $data = User::select(
                '*',
                DB::raw("CONCAT(photo_relative_path, '/', photo) AS photo"),
                DB::raw("CONCAT(sign_relative_path, '/', signature) AS signature")
            )
                ->where('ulb_id', $ulbId)
                ->orderBy('id');

            $userList = app(Pipeline::class)
                ->send(
                    $data
                )
                ->through([
                    SearchByName::class,
                    SearchByEmail::class,
                    SearchByMobile::class,
                    SearchByRole::class
                ])
                ->thenReturn()
                ->paginate($perPage);

            return responseMsgs(true, "User List", $userList, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    /**
     * | Multiple List User
     */
    public function multipleUserList(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                "ids" => 'required|array'
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $perPage = $req->perPage ?? 10;
            $ulbId = authUser()->ulb_id;
            $data = User::select(
                '*',
                DB::raw("CONCAT(photo_relative_path, '/', photo) AS photo"),
                DB::raw("CONCAT(sign_relative_path, '/', signature) AS signature")
            )
                ->where('ulb_id', $ulbId)
                ->whereIn('id', $req->ids)
                // ->orderByDesc('id')
                ->get();

            return responseMsgs(true, "User List", $data, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    /**
     * | List User
     */
    public function userById(Request $req)
    {
        try {
            $req->validate(
                ["id" => 'required']
            );
            $data = User::find($req->id);

            return responseMsgs(true, "User Data", $data, "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }


    /**
     * | Delete User
     */
    public function deleteUser(Request $request)
    {
        try {
            $request->validate(
                [
                    'id' => 'required',
                    'isSuspended' => 'required|boolean'
                ]
            );
            $adminRole = Config::get('constants.ADMIN_ROLE');
            $loggedInUser = authUser()->id;
            $mWfRoleUser = new WfRoleusermap();
            $roleIds = $mWfRoleUser->getRoleIdByUserId($loggedInUser)->pluck('wf_role_id')->toArray();                      // Model to () get Role By User Id
            if ($request->isAdmin) {
                if (!in_array($adminRole, $roleIds))
                    throw new Exception("chala ja");
            }

            $data = User::find($request->id);
            $data->suspended = $request->isSuspended;
            $data->save();

            return responseMsgs(true, "Status Changed Succesfully", '', "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }


    /**
     * |
     */
    // Changing Password
    public function changePass(ChangePassRequest $request)
    {
        try {
            $id = auth()->user()->id;
            $user = User::find($id);
            $validPassword = Hash::check($request->password, $user->password);
            if ($validPassword) {

                $user->password = Hash::make($request->newPassword);
                $user->save();
                $token = $request->user()->currentAccessToken();
                $token->expires_at = Carbon::now();
                $token->save();
                return responseMsgs(true, 'Successfully Changed the Password', "", "", "02", responseTime(), "POST", $request->deviceId);
            }
            throw new Exception("Old Password dosen't Match!");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "02", responseTime(), "POST", $request->deviceId);
        }
    }

    /**
     * | Change Password by OTP 
     * | Api Used after the OTP Validation
     */
    public function changePasswordByOtp(OtpChangePass $request)
    {
        try {
            $id = auth()->user()->id;
            $user = User::find($id);
            $user->password = Hash::make($request->password);
            $user->save();

            return responseMsgs(true, 'Successfully Changed the Password', "", "", "02", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", $request->deviceId);
        }
    }

    /**
     * | For Showing Logged In User Details 
     * | #user_id= Get the id of current user 
     * | if $redis available then get the value from redis key
     * | if $redis not available then get the value from sql database
     */
    public function myProfileDetails()
    {
        try {
            $userId = auth()->user()->id;
            $mUser = new User();
            $details = $mUser->getUserById($userId);
            $usersDetails = [
                'id'        => $details->id,
                'NAME'      => $details->name,
                'USER_NAME' => $details->user_name,
                'mobile'    => $details->mobile,
                'email'     => $details->email,
                'ulb_id'    => $details->ulb_id,
                'ulb_name'  => $details->ulb_name,
            ];

            return responseMsgs(true, "Data Fetched", $usersDetails, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }



    /**
     * | Get Users Details by Id
     */
    public function getUser(Request $request, $id)
    {
        try {
            $mUser = new User();
            $data = $mUser->getUserRoleDtls()
                ->where('users.id', $id)
                ->first();
            if (!$data)
                throw new Exception('No Role For the User');

            return responseMsgs(true, "User Details", $data, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    /**
     * | Get All User Details
     */
    public function getAllUsers(Request $request)
    {
        try {
            $mUser = new User();
            $ulbId = authUser()->ulb_id;
            $data = $mUser->getUserRoleDtls()
                ->where('users.ulb_id', $ulbId)
                ->orderbyDesc('users.id')
                ->get();
            if ($data->isEmpty())
                throw new Exception('No User Found');

            return responseMsgs(true, "User Details", $data, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    /**
     * |Employee Lis
     */
    public function employeeList()
    {
        try {
            $ulbId = authUser()->ulb_id;
            $data = User::select('name as user_name', 'id')
                ->where('user_type', 'Employee')
                ->where('ulb_id', $ulbId)
                ->orderBy('id')
                ->get();

            return responseMsgs(true, "List Employee", $data, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    /**
     * | Get user Notification
     */
    public function userNotification()
    {
        $user = authUser();
        $userId = $user->id;
        $ulbId = $user->ulb_id;
        $userType = $user->user_type;
        // $mMirrorUserNotification = new MirrorUserNotification();
        $mUserNotification = new UserNotification();
        if ($userType == 'Citizen') {
            $data = $mUserNotification->notificationByUserId()
                ->where('citizen_id', $userId)
                ->get();
            $notification = collect($data)->groupBy('category');
        } else
            $notification =  $mUserNotification->notificationByUserId($userId)
                ->where('user_id', $userId)
                ->where('ulb_id', $ulbId)
                ->get();

        if (collect($notification)->isEmpty())
            return responseMsgs(true, "No Current Notification", [], "010108", "1.0", responseTime(), "POST", "");

        return responseMsgs(true, "Your Notificationn", remove_null($notification), "010108", "1.0", responseTime(), "POST", "");
    }

    /**
     * | Add Notification
     */
    public function addNotification($req)
    {
        $user = authUser();
        $userId = $user->id;
        $ulbId = $user->ulb_id;
        $muserNotification = new UserNotification();

        $mreq = new Request([
            "user_id" => $req->userId,
            "citizen_id" => $req->citizenId,
            "notification" => $req->notification,
            "send_by" => $req->sender,
            "category" => $req->category,
            "sender_id" => $userId,
            "ulb_id" => $ulbId,
            "module_id" => $req->moduleId,
            "event_id" => $req->eventId,
            "generation_time" => Carbon::now(),
            "ephameral" => $req->ephameral,
            "require_acknowledgment" => $req->requireAcknowledgment,
            "expected_delivery_time" => null,
            "created_at" => Carbon::now(),
        ]);
        $id = $muserNotification->addNotification($mreq);

        if ($req->citizenId) {
            $data = $muserNotification->notificationByUserId($userId)
                ->where('citizen_id', $req->citizenId)
                ->get();
        } else
            $data = $muserNotification->notificationByUserId($userId)
                ->where('user_id', $req->userId)
                ->take(10);

        $this->addMirrorNotification($mreq, $id, $user);

        return responseMsgs(true, "Notificationn Addedd", '', "010108", "1.0", "", "POST", "");
    }

    /**
     * | Add Mirror Notification
     */
    public function addMirrorNotification($req, $id, $user)
    {
        $mMirrorUserNotification = new MirrorUserNotification();
        $mreq = new Request([
            "user_id" => $req->user_id,
            "citizen_id" => $req->citizen_id,
            "notification" => $req->notification,
            "send_by" => $req->send_by,
            "category" => $req->category,
            "sender_id" => $user->id,
            "ulb_id" => $user->ulb_id,
            "module_id" => $req->module_id,
            "event_id" => $req->event_id,
            "generation_time" => Carbon::now(),
            "ephameral" => $req->ephameral,
            "require_acknowledgment" => $req->require_acknowledgment,
            "expected_delivery_time" => $req->expected_delivery_time,
            "created_at" => Carbon::now(),
            "notification_id" => $id,
        ]);
        $mMirrorUserNotification->addNotification($mreq);
    }

    /**
     * | Get user Notification
     */
    public function deactivateNotification($req)
    {
        $muserNotification = new UserNotification();
        $muserNotification->deactivateNotification($req);

        return responseMsgs(true, "Notificationn Deactivated", '', "010108", "1.0", "", "POST", "");
    }

    /**
     * | For Hashing Password
     */
    public function hashPassword()
    {
        $datas =  User::select('id', 'password', "old_password")
            ->where('password', '121')
            ->orderby('id')
            ->get();

        foreach ($datas as $data) {
            $user = User::find($data->id);
            if (!$user || $user->password != '121') {
                continue;
            }
            DB::beginTransaction();
            $user->password = Hash::make($data->old_password);
            $user->update();
            DB::commit();
        }
    }

    /**
     * | List User Type
     */
    public function listUserType(Request $req)
    {
        $userType = Config::get('constants.USER_TYPE');
        return responseMsgs(true, "User Type", $userType);
    }

    /**
     * | List Admin
     */
    public function listAdmin(Request $req)
    {
        $userList = User::select(
            'users.id',
            'users.user_name',
            'users.mobile',
            'users.email',
            'users.name',
            'ulb_name',
            'suspended',
        )
            ->where('user_type', 'Admin')
            ->join('ulb_masters', 'ulb_masters.id', 'users.ulb_id')
            ->orderBy('name')
            ->get();
        return responseMsgs(true, "User List", $userList);
    }


    public function resetPassword(Request $req)
    {
        $validated = Validator::make($req->all(), [
            'userId' => 'required|digits_between:1,9223372036854775807',
            'newPassword' => [
                'nullable',
                'min:6',
                'max:255',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/'  // must contain a special character
            ]
        ]);
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $id = auth()->user()->id;
            $Muser = User::find($id);
            $respons = $this->getUser($req, $id);
            $respons = json_decode(json_encode($respons), true);
            if (!$respons["original"]["status"]) {
                throw new Exception("Unable To Find Your Role Dtl");
            }
            $RoleDtl = collect($respons["original"]["data"]);
            if (!in_array(strtoupper($RoleDtl["role_name"]), ["ADMIN", "SUPER ADMIN"])) {
                throw new Exception("You Are Not Authorized For This Action");
            }
            $user = User::find($id);
            if (!$user) {
                throw new Exception("Data Not Found");
            }
            $name = Str::upper((substr($user->name, 0, 4)));
            $mobi = $user->mobile ? substr($user->mobile, 6, 4) : "1234";
            if (!$req->newPassword) {
                $req->request->add(["newPassword" => ($name . "@" . $mobi)]);
            }
            // dd(request()->ip(),request()->path(),request()->header('User-Agent'),$req->all(),$user);
            DB::beginTransaction();
            $user->tokens->each(function ($token, $key) {
                $token->expires_at = Carbon::now();
                $token->save();
            });
            $user->password = Hash::make($req->newPassword);
            $user->save();
            DB::commit();
            return   responseMsgs(true, "Password Reset Successfully", remove_null($RoleDtl));
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }


    public function citizenDtls(Request $req)
    {
        try {
            $mWfRoleusermap = new WfRoleusermap();
            $citizen = Auth()->user();
            // $menuRoleDetails = $mWfRoleusermap->getRoleDetailsByUserId($user->id);
            // if (empty(collect($menuRoleDetails)->first())) {
            //     throw new Exception('User has No Roles!');
            // }
            $role = $citizen->user_type;
            $permittedWards = [];
            // $permittedWards = collect($permittedWards)->sortBy(function ($item) {
            //     // Extract the numeric part from the "ward_name"
            //     preg_match('/\d+/', $item->ward_name, $matches);
            //     return (int) ($matches[0] ?? "");
            // })->values();

            // $includeMenu = $this->_UserMenuMobileInclude->metaDtls()
            //     ->where("user_menu_mobile_includes.user_id", $user->id)
            //     ->where("user_menu_mobile_includes.is_active", true)
            //     ->get();
            // $excludeMenu = $this->_UserMenuMobileExclude->metaDtls()
            //     ->where("user_menu_mobile_excludes.user_id", $user->id)
            //     ->where("user_menu_mobile_excludes.is_active", true)
            //     ->get();
            // $userIncludeMenu = $this->_UserMenuMobileInclude->unionDataWithRoleMenu()
            //     ->where("user_menu_mobile_includes.user_id", $user->id)
            //     ->where("user_menu_mobile_includes.is_active", true)
            //     ->get();
            DB::enablequerylog();
            // $menuList = $this->_MenuMobileMaster->metaDtls()
            //     ->where("menu_mobile_masters.is_active", true)
            //     ->where(function ($query) {
            //         $query->OrWhere("menu_mobile_role_maps.is_active", true)
            //             ->orWhereNull("menu_mobile_role_maps.is_active");
            //     })
            //     ->WhereIn("menu_mobile_role_maps.role_id", ($menuRoleDetails)->pluck("roleId"));
            // if ($includeMenu->isNotEmpty()) {
            //     $menuList = $menuList->WhereNotIn("menu_mobile_masters.id", ($includeMenu)->pluck("menu_id"));
            // }

            // DB::enableQueryLog();
            // $menuList = collect(($menuList->get())->toArray());
            // foreach ($userIncludeMenu->toArray() as $val) {
            //     $menuList->push($val);
            // }
            // $menuList = collect($menuList->whereNotIn("id", ($excludeMenu)->pluck("menu_id"))->toArray());
            // $menuList = $menuList->map(function ($val) {

            //     return
            //         [
            //             "id"        =>  $val["id"],
            //             "role_id"   =>  $val["role_id"],
            //             "role_name" =>  $val["role_name"],
            //             "parent_id" =>  $val["parent_id"],
            //             "module_id" =>  $val["module_id"],
            //             "serial"    =>  $val["serial"],
            //             "menu_string" =>  $val["menu_string"],
            //             "route"      =>  $val["route"],
            //             "icon"       =>  $val["icon"],
            //             "is_sidebar" =>  $val["is_sidebar"],
            //             "is_menu"    =>  $val["is_menu"],
            //             "create"     =>  $val["create"],
            //             "read"       =>  $val["read"],
            //             "update"     =>  $val["update"],
            //             "delete"     =>  $val["delete"],
            //             "module_name" =>  $val["module_name"],
            //         ];
            // });

            $module = $this->_ModuleMaster->select("id", "module_name")->where("is_suspended", false)->OrderBy("id", "ASC")->get();
            $routList = collect();
            foreach ($module as $val) {
                $rout["layout"] = $val->module_name;
                // $rout["pages"] = $menuList->where("module_id", $val->id)->sortBy("serial")->values();
                $routList->push($rout);
            }

            $data['userDetails'] = $citizen;
            $data['userDetails']["imgFullPath"] = trim($citizen->relative_path . "/" . $citizen->profile_photo, "/");
            $data['userDetails']['role'] = $role;
            $data["routes"] = $routList;
            $data["permittedWard"] = $permittedWards;
            return responseMsgs(true, "You have Logged In Successfully", $data, 010101, "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", 010101, "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    public function ePramanCheck()
    {
        $data = EPramanExistCheck::select('id', 'is_epramaan')->get();
        return responseMsgs(true, "E Pramaan Detail", $data, 010101, "1.0", responseTime(), "POST", "");
    }
}
