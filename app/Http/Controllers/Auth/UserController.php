<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthorizeRequestUser;
use App\Http\Requests\Auth\AuthUserRequest;
use App\Http\Requests\Auth\ChangePassRequest;
use App\Http\Requests\Auth\OtpChangePass;
use App\MicroServices\DocUpload;
use App\Models\Auth\ActiveCitizen;
use App\Models\Auth\User;
use App\Models\EPramanExistCheck;
use App\Models\ModuleMaster;
use App\Models\Notification\MirrorUserNotification;
use App\Models\Notification\UserNotification;
use App\Models\UlbMaster;
use App\Models\UlbModulePermission;
use App\Models\UlbWardMaster;
use App\Models\UserLoginDetail;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;


use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\throwException;

class UserController extends Controller
{
    use Auth;
    private $_mUser;
    private $_MenuMobileMaster;
    private $_UserMenuMobileExclude;
    private $_UserMenuMobileInclude;
    private $_ModuleMaster;
    private $_UlbModulePermission;
    public function __construct()
    {
        $this->_mUser = new User();
        $this->_ModuleMaster = new ModuleMaster();
        $this->_UlbModulePermission = new UlbModulePermission();
        // $this->_MenuMobileMaster = new MenuMobileMaster();
        // $this->_UserMenuMobileExclude   = new UserMenuMobileExclude();
        // $this->_UserMenuMobileInclude   = new UserMenuMobileInclude();
    }

    /**
     * | User Login
     */
    // public function loginAuth(Request $req)
    // {
    //     $validated = Validator::make(
    //         $req->all(),
    //         [
    //             'email' => 'required|email',
    //             'password' => 'required',
    //             'type' => "nullable|in:mobile",
    //             'moduleId' => "nullable|int"
    //         ]
    //     );
    //     if ($validated->fails())
    //         return validationError($validated);

    //     try {
    //         // ✅ Rate Limiting: Allow 5 attempts per 120 seconds per IP
    //         // $rateKey = Str::lower('login|' . $req->ip());
    //         // if (RateLimiter::tooManyAttempts($rateKey, 5)) {
    //         //     $seconds = RateLimiter::availableIn($rateKey);
    //         //     return responseMsgs(false, "Too many login attempts. Try again in $seconds seconds.", '', 429, "1.0", responseTime(), "POST", $req->deviceId);
    //         // }

    //         // // Hit the rate limiter
    //         // RateLimiter::hit($rateKey, 120); // expires IN 2 MIN

    //         $secretKey = Config::get('constants.SECRETKEY');
    //         $email = $req->email;
    //         $encrypted = $req->password;
    //         $encryptedData = base64_decode($encrypted);
    //         $method = 'AES-256-CBC';
    //         $key = hash('sha256', $secretKey, true);
    //         $iv = substr(hash('sha256', $secretKey), 0, 16);

    //      return    $decrypted = openssl_decrypt($encryptedData, $method, $key, OPENSSL_RAW_DATA, $iv);
    //         if ($decrypted === false) {
    //             throw new Exception("Invalid Credentials");
    //         }
    //         $password = $decrypted;

    //         $mWfRoleusermap = new WfRoleusermap();
    //         $mUlbMaster = new UlbMaster();
    //         $user = $this->_mUser->getUserByEmail($email);

    //         if (!$user)
    //             throw new Exception("Invalid Credentials");

    //         if ($user->suspended == true)
    //             throw new Exception("You are not authorized to log in!");

    //         $checkUlbStatus = $mUlbMaster->checkUlb($user);
    //         if (!$checkUlbStatus) {
    //             throw new Exception('This Ulb is Restricted SuperAdmin!');
    //         }

    //         if ($req->moduleId) {
    //             $checkModule = $this->_UlbModulePermission->check($user, $req);
    //             if (!$checkModule) {
    //                 throw new Exception('Module is Restricted For This ulb!');
    //             }
    //         }

    //         if (Hash::check($password, $user->password)) {
    //             // ✅ Clear rate limit on success
    //             RateLimiter::clear($rateKey);

    //             $token = $user->createToken('my-app-token')->plainTextToken;
    //             $menuRoleDetails = $mWfRoleusermap->getRoleDetailsByUserId($user->id);
    //             $role = collect($menuRoleDetails)->pluck('roles');
    //             $roleId = collect($menuRoleDetails)->pluck('roleId');


    //             if (!$req->type && $this->checkMobileUserRole($menuRoleDetails)) {
    //                 throw new Exception("Mobile user not login as web user");
    //             }

    //             $jeRole = collect($menuRoleDetails)->where('roles', 'JUNIOR ENGINEER');
    //             if ($jeRole->isEmpty() && $req->type && !$this->checkMobileUserRole($menuRoleDetails)) {
    //                 throw new Exception("Web user not login as mobile user");
    //             }

    //             if (in_array($user->user_type, ['TC', 'TL'])) {
    //                 $userlog = new UserLoginDetail();
    //                 $userlog->user_id = $user->id;
    //                 $userlog->login_date = now()->format("Y-m-d");
    //                 $userlog->login_time = now()->format("h:i:s a");
    //                 $userlog->ip_address = $req->ip();
    //                 $userlog->save();
    //             }

    //             $user->ulbName = UlbMaster::find($user->ulb_id)->ulb_name ?? "";
    //             $data['token'] = $token;
    //             $data['userDetails'] = $user;
    //             $data['userDetails']['role'] = $role;
    //             $data['userDetails']['roleId'] = $roleId;

    //             return responseMsgs(true, "You have Logged In Successfully", $data, 010101, "1.0", responseTime(), "POST", $req->deviceId)
    //                 ->cookie(
    //                     'auth_token',                // cookie name
    //                     $token,                      // cookie value
    //                     120,                         // expiration in minutes
    //                     '/',                          // path
    //                     '.jharkhandegovernance.com', // domain
    //                     true,                        // secure (HTTPS only)
    //                     true,                        // HttpOnly
    //                     false,                       // raw
    //                     'Strict'                     // SameSite
    //                 );
    //         }

    //         throw new Exception("Invalid Credentials");
    //     } catch (Exception $e) {
    //         return responseMsg(false, $e->getMessage(), '');
    //     }
    // }

    // public function loginAuth(Request $req)
    // {
    //     $validated = Validator::make(
    //         $req->all(),
    //         [
    //             'email' => 'required|email',
    //             'password' => 'required',
    //             'type' => 'nullable|in:mobile',
    //             'moduleId' => 'nullable|int',
    //             'captcha_code' => 'nullable|string',
    //             'captcha_id' => 'nullable|string',
    //             'systemUniqueId' => 'nullable|string',
    //         ]
    //     );

    //     if ($validated->fails())
    //         return validationError($validated);

    //     try {
    //         // ✅ Common encryption setup
    //         $secretKey = Config::get('constants.SECRETKEY');
    //         $method = 'AES-256-CBC';
    //         $key = hash('sha256', $secretKey, true);
    //         $iv = substr(hash('sha256', $secretKey), 0, 16);

    //         // ✅ Captcha verification (only for modules requiring captcha)
    //         $captchaModules = Config::get('constants.MODULES_WITH_CAPTCHA', []);
    //         if ($req->filled('moduleId') && in_array($req->moduleId, $captchaModules)) {

    //             $storedCode = Redis::get("CAPTCHA:{$req->captcha_id}");
    //             if (!$storedCode) {
    //                 throw new Exception("Captcha expired or not found");
    //             }

    //             // Decrypt the frontend-provided captcha
    //             $decryptedCaptcha = openssl_decrypt(
    //                 base64_decode($req->captcha_code),
    //                 $method,
    //                 $key,
    //                 OPENSSL_RAW_DATA,
    //                 $iv
    //             );

    //             // Compare the decrypted frontend captcha with stored Redis captcha
    //             if (strtoupper(trim($storedCode)) !== strtoupper(trim($decryptedCaptcha))) {
    //                 throw new Exception("Incorrect captcha code");
    //             }

    //             // Delete used captcha
    //             Redis::del("CAPTCHA:{$req->captcha_id}");
    //         }

    //         // return responseMsgs(true, "Invalid Credentials", "", 10101, "1.0", responseTime(), "POST", $req->deviceId);
    //         // ✅ Rate Limiting: max 5 attempts per 120 seconds per IP
    //         // $rateKey = Str::lower('login|' . $user->ip());
    //         // $rateKey = 'login:' . $user->id;
    //         $clientUniqueId = $req->systemUniqueId;
    //         $rateKey = 'login:' . $clientUniqueId;
    //         if (RateLimiter::tooManyAttempts($rateKey, 5)) {
    //             $seconds = RateLimiter::availableIn($rateKey);
    //             return responseMsgs(false, "Too many login attempts. Try again in $seconds seconds.", '', 429, "1.0", responseTime(), "POST", $req->deviceId);
    //         }

    //         RateLimiter::hit($rateKey, 120); // 2 minutes limit window

    //         // ✅ Decrypt user password
    //         $encryptedData = base64_decode($req->password);
    //         $password = openssl_decrypt($encryptedData, $method, $key, OPENSSL_RAW_DATA, $iv);
    //         if ($password === false) {
    //             throw new Exception("Invalid Credentials");
    //         }

    //         // ✅ User lookup and checks
    //         $mWfRoleusermap = new WfRoleusermap();
    //         $mUlbMaster = new UlbMaster();
    //         $user = $this->_mUser->getUserByEmail($req->email);

    //         if (!$user)
    //             throw new Exception("Invalid Credentials");
    //         if ($user->suspended == true)
    //             throw new Exception("You are not authorized to log in!");

    //         $checkUlbStatus = $mUlbMaster->checkUlb($user);
    //         if (!$checkUlbStatus) {
    //             throw new Exception('This ULB is restricted for SuperAdmin!');
    //         }

    //         if ($req->moduleId) {
    //             $checkModule = $this->_UlbModulePermission->check($user, $req);
    //             if (!$checkModule) {
    //                 throw new Exception('Module is restricted for this ULB!');
    //             }
    //         }

    //         // ✅ Password validation
    //         if (Hash::check($password, $user->password)) {

    //             // ✅ Clear rate limiter after successful login
    //             RateLimiter::clear($rateKey);

    //             $token = $user->createToken('my-app-token')->plainTextToken;
    //             $menuRoleDetails = $mWfRoleusermap->getRoleDetailsByUserId($user->id);
    //             $role = collect($menuRoleDetails)->pluck('roles');
    //             $roleId = collect($menuRoleDetails)->pluck('roleId');

    //             if (!$req->type && $this->checkMobileUserRole($menuRoleDetails)) {
    //                 throw new Exception("Mobile user cannot login as web user");
    //             }

    //             $jeRole = collect($menuRoleDetails)->where('roles', 'JUNIOR ENGINEER');
    //             if ($jeRole->isEmpty() && $req->type && !$this->checkMobileUserRole($menuRoleDetails)) {
    //                 throw new Exception("Web user cannot login as mobile user");
    //             }

    //             // ✅ Log login for TC/TL users
    //             if (in_array($user->user_type, ['TC', 'TL'])) {
    //                 UserLoginDetail::create([
    //                     'user_id' => $user->id,
    //                     'login_date' => now()->format('Y-m-d'),
    //                     'login_time' => now()->format('h:i:s a'),
    //                     'ip_address' => $req->ip(),
    //                 ]);
    //             }

    //             $user->ulbName = UlbMaster::find($user->ulb_id)->ulb_name ?? "";
    //             $data['token'] = $token;
    //             $data['userDetails'] = $user;
    //             $data['userDetails']['role'] = $role;
    //             $data['userDetails']['roleId'] = $roleId;
    //             if ($user->asset_type_id) {
    //                 $data['userDetails']['asset_type_id'] = array_map('intval', explode(',', trim($user->asset_type_id, '{}'))); 
    //             }

    //             return responseMsgs(true, "You have logged in successfully", $data, 10101, "1.0", responseTime(), "POST", $req->deviceId)
    //                 ->cookie(
    //                     'auth_token',
    //                     $token,
    //                     120,
    //                     '/',
    //                     '.jharkhandegovernance.com',
    //                     true,
    //                     true,
    //                     false,
    //                     'Strict'
    //                 );
    //         }

    //         throw new Exception("Invalid Credentials");
    //     } catch (Exception $e) {
    //         return responseMsg(false, $e->getMessage(), '');
    //     }
    // }

    public function loginAuth(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
                'type' => 'nullable|in:mobile',
                'moduleId' => 'nullable|int',
                'captcha_code' => 'nullable|string',
                'captcha_id' => 'nullable|string',
                'systemUniqueId' => 'nullable|string',
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {

            /* =========================================================
            * Encryption Setup
            * ========================================================= */
            $secretKey = Config::get('constants.SECRETKEY');
            $method = 'AES-256-CBC';
            $key = hash('sha256', $secretKey, true);
            $iv = substr(hash('sha256', $secretKey), 0, 16);

            /* =========================================================
            * Normalize Module ID
            * ========================================================= */
            $moduleId = (int) ($req->moduleId ?? 0);

            /* =========================================================
            * Captcha Verification
            * ========================================================= */
            $captchaModules = Config::get('constants.MODULES_WITH_CAPTCHA', []);
            if ($req->filled('moduleId') && in_array($moduleId, $captchaModules)) {

                $storedCode = Redis::get("CAPTCHA:{$req->captcha_id}");
                if (!$storedCode) {
                    throw new Exception("Captcha expired or not found");
                }

                $decryptedCaptcha = openssl_decrypt(
                    base64_decode($req->captcha_code),
                    $method,
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv
                );

                if (strtoupper(trim($storedCode)) !== strtoupper(trim($decryptedCaptcha))) {
                    throw new Exception("Incorrect captcha code");
                }

                Redis::del("CAPTCHA:{$req->captcha_id}");
            }

            /* =========================================================
            * Rate Limiting
            * ========================================================= */
            $rateKey = 'login:' . $req->systemUniqueId;

            if (RateLimiter::tooManyAttempts($rateKey, 5)) {
                $seconds = RateLimiter::availableIn($rateKey);
                return responseMsgs(
                    false,
                    "Too many login attempts. Try again in $seconds seconds.",
                    '',
                    429,
                    "1.0",
                    responseTime(),
                    "POST",
                    $req->deviceId
                );
            }

            RateLimiter::hit($rateKey, 120);

            /* =========================================================
            * Password Decryption
            * ========================================================= */
            $password = openssl_decrypt(
                base64_decode($req->password),
                $method,
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($password === false) {
                throw new Exception("Invalid Credentials");
            }

            /* =========================================================
            * User Lookup
            * ========================================================= */
            $user = $this->_mUser->getUserByEmail($req->email);
            if (!$user) {
                throw new Exception("Invalid Credentials");
            }

            if ($user->suspended) {
                throw new Exception("You are not authorized to log in!");
            }

            /* =========================================================
            * ULB → MODULE Restriction (SKIP for moduleId = 0 or 35)
            * ========================================================= */
            if ($moduleId > 0 && !in_array($moduleId, [35])) {
                if (!$this->_UlbModulePermission->check($user, $req)) {
                    throw new Exception("Module is restricted for this ULB!");
                }
            }

            /* =========================================================
            * USER → MODULE Permission
            * ========================================================= */
            if ($moduleId > 0) {
                if (!$this->hasModulePermission($user->id, $moduleId)) {
                    throw new Exception("Permission denied!");
                }
            }

            /* =========================================================
            * Password Match
            * ========================================================= */
            if (!Hash::check($password, $user->password)) {
                throw new Exception("Invalid Credentials");
            }

            /* =========================================================
            * SUCCESS LOGIN
            * ========================================================= */
            RateLimiter::clear($rateKey);

            $token = $user->createToken('my-app-token')->plainTextToken;

            $mWfRoleusermap = new WfRoleusermap();
            $menuRoleDetails = $mWfRoleusermap->getRoleDetailsByUserId($user->id);

            $role = collect($menuRoleDetails)->pluck('roles');
            $roleId = collect($menuRoleDetails)->pluck('roleId');

            if (!$req->type && $this->checkMobileUserRole($menuRoleDetails)) {
                throw new Exception("Mobile user cannot login as web user");
            }

            if ($req->type && !$this->checkMobileUserRole($menuRoleDetails)) {
                throw new Exception("Web user cannot login as mobile user");
            }

            /* =========================================================
            * TC / TL Login Log
            * ========================================================= */
            if (in_array($user->user_type, ['TC', 'TL'])) {
                UserLoginDetail::create([
                    'user_id' => $user->id,
                    'login_date' => now()->format('Y-m-d'),
                    'login_time' => now()->format('h:i:s a'),
                    'ip_address' => $req->ip(),
                ]);
            }

            /* =========================================================
            * Response Data
            * ========================================================= */
            $user->ulbName = UlbMaster::find($user->ulb_id)->ulb_name ?? "";

            $data['token'] = $token;
            $data['userDetails'] = $user;
            $data['userDetails']['role'] = $role;
            $data['userDetails']['roleId'] = $roleId;

            if ($user->asset_type_id) {
                $data['userDetails']['asset_type_id'] =
                    array_map('intval', explode(',', trim($user->asset_type_id, '{}')));
            }

            return responseMsgs(
                true,
                "You have logged in successfully",
                $data,
                10101,
                "1.0",
                responseTime(),
                "POST",
                $req->deviceId
            )->cookie(
                'auth_token',
                $token,
                120,
                '/',
                '.jharkhandegovernance.com',
                true,
                true,
                false,
                'Strict'
            );

        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), '');
        }
    }


    private function hasModulePermission(int $userId, int $moduleId): bool
    {
        return DB::table('menu_roleusermaps as mur')
            ->join('menu_roles as mr', 'mr.id', '=', 'mur.menu_role_id')
            ->where('mur.user_id', $userId)
            ->where('mr.module_id', $moduleId)
            ->where('mur.is_suspended', false)
            ->where('mr.is_suspended', false)
            ->exists();
    }


    public function changePass(ChangePassRequest $request)
    {
        try {
            $id = auth()->user()->id;
            $user = User::find($id);

            // 🔐 AES Decryption of Old Password
            $secretKey = Config::get('constants.SECRETKEY');
            $encryptedOld = $request->password;
            $encryptedOldData = base64_decode($encryptedOld);
            $method = 'AES-256-CBC';
            $key = hash('sha256', $secretKey, true);
            $iv = substr(hash('sha256', $secretKey), 0, 16);

            $decryptedOld = openssl_decrypt($encryptedOldData, $method, $key, OPENSSL_RAW_DATA, $iv);
            if ($decryptedOld === false) {
                throw new Exception("Old password decryption failed or tampered data");
            }

            // 🔁 Hash (SHA256) after decryption
            // $hashedOldPassword = Hash::make($decryptedOld);
            // ✅ Validate old password
            if (!Hash::check($decryptedOld, $user->password)) {
                throw new Exception("Old Password doesn't Match!");
            }

            // 🔐 AES Decryption of New Password
            $encryptedNew = $request->newPassword;
            $encryptedNewData = base64_decode($encryptedNew);

            $decryptedNew = openssl_decrypt($encryptedNewData, $method, $key, OPENSSL_RAW_DATA, $iv);
            if ($decryptedNew === false) {
                throw new Exception("New password decryption failed or tampered data");
            }

            // 🔁 Hash (SHA256) and then Hash::make for DB
            // $hashedNewPassword = hash('sha256', $decryptedNew);
            $user->password = Hash::make($decryptedNew);
            $user->save();

            // 🔒 Optionally expire current token
            $token = $request->user()->currentAccessToken();
            $token->expires_at = Carbon::now();
            $token->save();

            return responseMsgs(true, 'Successfully Changed the Password', "", "", "02", responseTime(), "POST", $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "02", responseTime(), "POST", $request->deviceId);
        }
    }

    /**
     * | User role details by User Id
     */
    public function getUserRoleId(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'id' => 'required|int'
            ]
        );
        if ($validated->fails())
            return validationError($validated);
        try {
            $user = $this->_mUser->getUserById($req->id);
            if (!$user)
                throw new Exception("Invalid Credentials");
            $mWfRoleusermap = new WfRoleusermap();
            $menuRoleDetails = $mWfRoleusermap->getRoleDetailsByUserId($req->id);

            $role = collect($menuRoleDetails)->map(function ($value, $key) {
                $values = $value['roles'];
                return $values;
            });
            $roleId = collect($menuRoleDetails)->map(function ($value, $key) {
                $values = $value['roleId'];
                return $values;
            });
            $data['userDetails'] = $user;
            $data['userDetails']['role'] = $role;
            $data['userDetails']['roleId'] = $roleId;
            return responseMsgs(true, "Data Retrieved", $data, "120503", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120503", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }



    private function checkMobileUserRole($menuRoleDetails)
    {

        foreach ($menuRoleDetails as $role) {
            if (in_array($role->roles, ['TAX COLLECTOR', 'ULB TAX COLLECTOR', 'TAX DAROGA', 'DRIVER', 'SEPTIC TANKER DRIVER', 'ENFORCEMENT OFFICER', "CONDUCTOR", "PARKING INCHARGE", "LAMS FieldOfficer", "BUS CONDUCTOR", "Municipal Rental Tc"])) {

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
    // public function createUser(AuthUserRequest $request)
    // {
    //     try {
    //         // Validation---@source-App\Http\Requests\AuthUserRequest
    //         $user = new User;
    //         $checkEmail = User::where('email', $request->email)->first();
    //         if ($checkEmail)
    //             throw new Exception('The email has already been taken.');
    //         $this->saving($user, $request);                     #_Storing data using Auth trait
    //         $firstname = explode(" ", $request->name);
    //         $user->user_name = $firstname[0] . '.' . substr($request->mobile, 0, 3);
    //         $user->password = Hash::make($firstname[0] . '@' . substr($request->mobile, 7, 3));

    //         DB::beginTransaction();
    //         $user->save();
    //         if ($request->role == 'ADMIN') {
    //             $this->assignRole($request->role, $user);
    //         }
    //         DB::commit();
    //         $data['id'] = $user->id;
    //         $data['userName'] = $user->user_name;
    //         return responseMsgs(true, "User Registered Successfully !! Please Continue to Login.
    //         Your Password is Your first name @ Your last 3 digit of your Mobile No", $data);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    /*
        |--------------------------------------------------------------------------
        | PASSWORD LOGIC (AS DISCUSSED)
        |--------------------------------------------------------------------------
        | password = FirstName + '@' + last mobile digits
        | minimum length = 8
        | mobile digits added dynamically
    */

    public function createUser(AuthUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = new User;

            // Check email uniqueness
            if (User::where('email', $request->email)->exists()) {
                throw new Exception('The email has already been taken.');
            }

            // Store common fields (Auth trait)
            $this->saving($user, $request);

            // Username generation
            $nameParts = explode(" ", trim($request->name));
            $firstName = $nameParts[0];
            $user->user_name = $firstName . '.' . substr($request->mobile, 0, 3);
            
            $basePassword = $firstName . '@';
            $baseLength   = strlen($basePassword);
            $minLength    = 8;

            if ($baseLength < $minLength) {
                // Add only required digits to reach minimum length
                $digitsUsed   = $minLength - $baseLength;
                $mobileDigits = substr($request->mobile, -$digitsUsed);
            } else {
                // For long names, always add last 3 digits
                $digitsUsed   = 3;
                $mobileDigits = substr($request->mobile, -3);
            }

            $finalPassword = $basePassword . $mobileDigits;

            // Hash password before saving
            $user->password = Hash::make($finalPassword);

            // Save user
            $user->save();

            // Assign role if ADMIN
            if ($request->role === 'ADMIN') {
                $this->assignRole($request->role, $user);
            }

            DB::commit();

            // RESPONSE MESSAGE WITH ACTUAL PASSWORD
            $message = "User Registered Successfully !! Please Continue to Login.
                        Your Password is {$finalPassword}";

            return responseMsgs(
                true,
                $message,
                [
                    'id' => $user->id,
                    'userName' => $user->user_name
                ]
            );

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
     * | Update User Details
     */
    public function updateClintIdByUser(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                "clientId" => 'nullable'
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $user = authUser();
            $id   = $user->id;
            // $user = User::find($id);
            $aciveCitizen = new ActiveCitizen();
            $aciveCitizendtl = $aciveCitizen->getCitizenById($id);
            $aciveCitizen->updateClientId($id, $request, $aciveCitizendtl);
            return responseMsgs(true, "Successfully Updated CientId", "", "", "01", responseTime(), "POST", "");
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
     * | List User
     */
    public function listUserByUlbId(Request $req)
    {
        $req->validate([
            'ulbId' => 'required'
        ]);

        try {
            $perPage = $req->perPage ?? 10;
            $ulbId = $req->ulbId;

            if ($ulbId == null) {
                throw new Exception('Please Provide Ulb Id!');
            }

            // Base query
            $query = User::select(
                'id',
                'user_name',
                'mobile',
                'email',
                'user_type',
                'name',
                'address',
                'alternate_mobile',
                'suspended',
                'reference_no',
                DB::raw("CONCAT(photo_relative_path, '/', photo) AS photo"),
                DB::raw("CONCAT(sign_relative_path, '/', signature) AS signature")
            )
                ->where('users.ulb_id', $ulbId)
                ->orderBy('users.id', 'desc');

            // Apply pipeline filters
            $filteredQuery = app(Pipeline::class)
                ->send($query)
                ->through([
                    SearchByName::class,
                    SearchByEmail::class,
                    SearchByMobile::class,
                    SearchByRole::class
                ])
                ->thenReturn();

            // Paginate results
            $userList = $filteredQuery->paginate($perPage);

            // Attach document data to each user
            $docUpload = new DocUpload();
            $data = $userList->map(function ($user) use ($docUpload) {
                $docDetails = $docUpload->getSingleDocUrl($user); // Fetch document details
                $docUrl = $docDetails['doc_path'] ?? null;

                return [
                    'id'             => $user->id,
                    'user_name'      => $user->user_name,
                    'mobile'         => $user->mobile,
                    'alternateMobile' => $user->alternate_mobile,
                    'email'          => $user->email,
                    'name'           => $user->name,
                    'user_type'      => $user->user_type,
                    'address'        => $user->address,
                    'suspended'      => $user->suspended,
                    'referenceNo'    => $user->reference_no,
                    'photo'          => $user->photo,
                    'signature'      => $user->signature,
                    'documentUrl'    => $docUrl, // Add document URL to response
                ];
            });

            // Build response structure
            $userListResponse = [
                "current_page" => $userList->currentPage(),
                "last_page"    => $userList->lastPage(),
                "data"         => $data,
                "total"        => $userList->total(),
            ];

            return responseMsgs(true, "User List", $userListResponse, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }
    /**
     * | List User 
     * | Filter By Role Id 75,76,77
     */
    public function listUserByUlbIdv1(Request $req)
    {
        $req->validate([
            'ulbId' => 'required'
        ]);

        try {
            $perPage = $req->perPage ?? 10;
            $ulbId = $req->ulbId;

            if ($ulbId == null) {
                throw new Exception('Please Provide Ulb Id!');
            }

            // Base query
            $query = User::select(
                'users.id',
                'user_name',
                'mobile',
                'email',
                'user_type',
                'name',
                'address',
                'alternate_mobile',
                'suspended',
                'reference_no',
                'wf_roleusermaps.wf_role_id as role_id',
                DB::raw("CONCAT(photo_relative_path, '/', photo) AS photo"),
                DB::raw("CONCAT(sign_relative_path, '/', signature) AS signature")
            )
                ->leftjoin('wf_roleusermaps', 'wf_roleusermaps.user_id', '=', 'users.id')
                ->where('users.ulb_id', $ulbId)
                ->whereIn('wf_roleusermaps.wf_role_id', [75, 76, 77]) // Filter roles to include only 75 and 76
                ->where('wf_roleusermaps.is_suspended', false)
                ->orderBy('id', 'desc');

            // Apply pipeline filters
            $filteredQuery = app(Pipeline::class)
                ->send($query)
                ->through([
                    SearchByName::class,
                    SearchByEmail::class,
                    SearchByMobile::class,
                    SearchByRole::class
                ])
                ->thenReturn();

            // Paginate results
            $userList = $filteredQuery->paginate($perPage);

            // Attach document data to each user
            $docUpload = new DocUpload();
            $data = $userList->map(function ($user) use ($docUpload) {
                $docDetails = $docUpload->getSingleDocUrl($user); // Fetch document details
                $docUrl = $docDetails['doc_path'] ?? null;

                return [
                    'id'             => $user->id,
                    'user_name'      => $user->user_name,
                    'mobile'         => $user->mobile,
                    'alternateMobile' => $user->alternate_mobile,
                    'email'          => $user->email,
                    'name'           => $user->name,
                    'user_type'      => $user->user_type,
                    'address'        => $user->address,
                    'suspended'      => $user->suspended,
                    'referenceNo'    => $user->reference_no,
                    'photo'          => $user->photo,
                    'signature'      => $user->signature,
                    'documentUrl'    => $docUrl, // Add document URL to response
                    'role_id'        => $user->role_id,
                ];
            });

            // Build response structure
            $userListResponse = [
                "current_page" => $userList->currentPage(),
                "last_page"    => $userList->lastPage(),
                "data"         => $data,
                "total"        => $userList->total(),
            ];

            return responseMsgs(true, "User List", $userListResponse, "", "01", responseTime(), "POST", "");
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
     * | Get Users Details by AUth 
     */
    public function getUserv1(Request $req)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return responseMsgs(false, "Unauthorized access", [], "", "01", responseTime(), "POST", "");
            }

            $permittedWards = [];
            $response       = [];
            $routList       = collect();

            // Fetch user details
            $lastLogin = User::select(
                'users.*',
                'ulb_masters.ulb_name',
                'wf_roles.id as wfRoleId',
                'wf_roles.role_name'
            )
                ->where('users.id', $user->id)
                ->join('ulb_masters', 'ulb_masters.id', '=', 'users.ulb_id')
                ->leftjoin('wf_roleusermaps', 'wf_roleusermaps.user_id', '=', 'users.id')
                ->leftjoin('wf_roles', 'wf_roles.id', '=', 'wf_roleusermaps.wf_role_id')
                ->where('suspended', false)
                ->where('wf_roleusermaps.is_suspended', false)
                ->first();

            if (!$lastLogin) {
                return responseMsgs(false, "User not found or suspended", [], "", "01", responseTime(), "POST", "");
            }

            // Fetch document details
            $docUpload  = new DocUpload();
            $docDetails = $docUpload->getSingleDocUrl($lastLogin);
            $docUrl     = $docDetails['doc_path'] ?? null;

            // Construct response
            $response = [
                '_id'              => $lastLogin->id,
                'ulbId'            => $lastLogin->ulb_id,
                'userName'         => $lastLogin->user_name,
                'mobile'           => $lastLogin->mobile,
                'alternateMobile'  => $lastLogin->alternate_mobile,
                'email'            => $lastLogin->email,
                'name'             => $lastLogin->name,
                'address'          => $lastLogin->address,
                'ulbName'          => $lastLogin->ulb_name,
                'suspended'        => $lastLogin->suspended,
                'referenceNo'      => $lastLogin->reference_no,
                'imgFullPath'      => $docUrl,
                'fullName'         => $lastLogin->name,
                'designation'      => $lastLogin->user_type,
                'status'           => $lastLogin->status,
                // 'imageUrl'         => $lastLogin->photo_path,
                'lastVisitedTime'  => $lastLogin->login_time,
                'lastVisitedDate'  => $lastLogin->login_date ? date('d-m-Y', strtotime($lastLogin->login_date)) : null,
                'lastIpAddress'    => $lastLogin->ip_address,
                'role'             => $lastLogin->user_type,
                'userTypeId'       => $lastLogin->user_type_id ?? $lastLogin->wfRoleId,
                'routes'           => $routList,
                'permittedWard'    => $permittedWards,
                'roleId'           => $lastLogin->wfRoleId,
                'roleName'         => $lastLogin->role_name,
                'empId'            => $lastLogin->emp_id,
            ];

            return responseMsgsv1(true,  true, "User Details", $response, "", "01", responseTime(), "POST", "");
        } catch (\Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), "POST", "");
        }
    }
    // get user which role are in judge or advocate

    public function getUserv2(Request $req)
    {
        try {

            $user = auth()->user();
            if (!$user) {
                return responseMsgs(false, "Unauthorized access", [], "", "01", responseTime(), "POST", "");
            }
            $ulbId = $user->ulb_id;
            $users = User::select(
                'users.*',
                'ulb_masters.ulb_name',
                'wf_roles.id as wfRoleId',
                'wf_roles.role_name'
            )
                ->join('ulb_masters', 'ulb_masters.id', '=', 'users.ulb_id')
                ->leftJoin('wf_roleusermaps', 'wf_roleusermaps.user_id', '=', 'users.id')
                ->leftJoin('wf_roles', 'wf_roles.id', '=', 'wf_roleusermaps.wf_role_id')
                ->whereIn('wf_roles.id', [75, 76])
                ->where('users.ulb_id', $ulbId)
                ->where('users.suspended', false)
                ->get();

            if ($users->isEmpty()) {
                return responseMsgs(false, "No users found for the specified roles", [], "", "01", responseTime(), "POST", "");
            }

            $docUpload = new DocUpload();
            $response = [];

            foreach ($users as $user) {
                $docDetails = $docUpload->getSingleDocUrl($user);
                $docUrl = $docDetails['doc_path'] ?? null;

                $response[] = [
                    '_id'              => $user->id,
                    'ulbId'            => $user->ulb_id,
                    'userName'         => $user->user_name,
                    'mobile'           => $user->mobile,
                    'alternateMobile'  => $user->alternate_mobile,
                    'email'            => $user->email,
                    'name'             => $user->name,
                    'address'          => $user->address,
                    'ulbName'          => $user->ulb_name,
                    'suspended'        => $user->suspended,
                    'referenceNo'      => $user->reference_no,
                    'imgFullPath'      => $docUrl,
                    'fullName'         => $user->name,
                    'designation'      => $user->user_type,
                    'status'           => $user->status,
                    'lastVisitedTime'  => $user->login_time,
                    'lastVisitedDate'  => $user->login_date ? date('d-m-Y', strtotime($user->login_date)) : null,
                    'lastIpAddress'    => $user->ip_address,
                    'role'             => $user->user_type,
                    'userTypeId'       => $user->user_type_id ?? $user->wfRoleId,
                    'routes'           => collect(), // or populate if needed
                    'permittedWard'    => [],        // or populate if needed
                    'roleId'           => $user->wfRoleId,
                    'roleName'         => $user->role_name,
                ];
            }

            return responseMsgsv1(true, true, "User Details List", $response, "", "01", responseTime(), "POST", "");
        } catch (\Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "01", responseTime(), "POST", "");
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
     * | Search Users with filters
     */
    public function searchUsersDetails(Request $request)
    {
        try {
            $request->validate([
                'filterBy' => 'nullable|string',
                'parameter' => 'nullable|string',
                'perPage' => 'nullable|integer',
            ]);

            $perPage = $request->perPage ?? 10;
            $filterBy = $request->filterBy;
            $parameter = $request->parameter;

            $ulbId = authUser()->ulb_id;
            $mUser = new User();

            $query = $mUser->getUserRoleDtls1($ulbId);

            if ($filterBy && $parameter) {
                switch ($filterBy) {
                    case "email":
                        $query->where('users.email', 'like', '%' . $parameter . '%');
                        break;
                    case "role":
                        $query->where('wf_roles.id', $parameter);
                        break;
                    case "module":
                        $query->where('ulb_module_permissions.module_id', $parameter);
                        break;
                }
            }
            $data = $query->orderbyDesc('users.id')->paginate($perPage);

            if ($data->isEmpty()) throw new Exception('No User Found');

            $list = ["current_page" => $data->currentPage(), "last_page" => $data->lastPage(), "data" => $data->items(), "total" => $data->total(),];
            
            return responseMsgs(true, "User Details", $list, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    /**
     * | Get Roles List
     */
    public function getRolesList()
    {
        try {
            $data = DB::table('wf_roles')
                ->select('id', 'role_name')
                ->where('is_suspended', false)
                ->orderBy('role_name')
                ->get();

            if ($data->isEmpty())
                throw new Exception('No Roles Found');

            return responseMsgs(true, "Roles List", $data, "", "01", responseTime(), "POST", "");
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
    /**
     * | List Admin
     */
    // public function listAdminv1(Request $req)
    // {
    //     $ulbId = $req->ulbId;
    //     $userList = User::select(
    //         'users.id',
    //         'ulb_masters.id as ulbId',
    //         'users.user_name',
    //         'users.mobile',
    //         'users.email',
    //         'users.name',
    //         'users.address',
    //         'ulb_name',
    //         'suspended',
    //     )
    //         ->where('user_type', 'Admin')
    //         ->where('ulb_id', $ulbId)
    //         ->join('ulb_masters', 'ulb_masters.id', 'users.ulb_id')
    //         ->orderBy('name')
    //         ->get();
    //     return responseMsgs(true, "User List", $userList);
    // }
    public function searchUsers(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'filterBy'  => 'nullable',
                'parameter' => 'nullable',
                'perPage'   => 'nullable',
            ]
        );

        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $key       = $request->filterBy;
            $parameter = $request->parameter;
            $ulbId     = $request->ulbId;
            $pages          = $request->perPage ?? 10;

            $query = User::select(
                'users.id',
                'ulb_masters.id as ulbId',
                'users.user_name',
                'users.mobile',
                'users.alternate_mobile',
                'users.email',
                'users.name',
                'users.address',
                'ulb_name',
                'suspended',
                'reference_no'
            )
                ->join('ulb_masters', 'ulb_masters.id', '=', 'users.ulb_id')
                ->where('ulb_masters.id', $ulbId)
                ->where('users.user_type', 'Admin')
                ->orderBy('users.name');
            if ($key != null) {
                switch ($key) {
                    case "name":
                        $query->where('users.name', 'like', '%' . $parameter . '%');
                        break;
                    case "email":
                        $query->where('users.email', 'like', '%' . $parameter . '%');
                        break;
                    case "mobile":
                        $query->where('users.mobile', 'like', '%' . $parameter . '%');
                        break;
                    default:
                        throw new Exception("Invalid filterBy value provided!");
                }
            }

            $inboxDetails = $query->paginate($pages);
            // Attach document data to each user
            $docUpload = new DocUpload;
            $data = $inboxDetails->map(function ($user) use ($docUpload) {
                $docDetails = $docUpload->getSingleDocUrl($user); // Fetch document details
                $docUrl = $docDetails['doc_path'] ?? null;

                return [
                    'id'             => $user->id,
                    'ulbId'          => $user->ulbId,
                    'userName'       => $user->user_name,
                    'mobile'         => $user->mobile,
                    'alternateMobile' => $user->alternate_mobile,
                    'email'          => $user->email,
                    'name'           => $user->name,
                    'address'        => $user->address,
                    'ulbName'        => $user->ulb_name,
                    'suspended'      => $user->suspended,
                    'referenceNo'    => $user->reference_no,
                    'documentUrl'    => $docUrl, // Add the document URL to the response
                ];
            });


            if ($inboxDetails->isEmpty()) {
                return responseMsgs(false, "No users found for the given filters!", []);
            }
            $list = [
                "current_page" => $inboxDetails->currentPage(),
                "last_page"    => $inboxDetails->lastPage(),
                "data"         => $data,
                "total"        => $inboxDetails->total(),
            ];

            return responseMsgs(true, "Admin List", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
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
    /**
     old login auth function
     */
    // public function loginAuth(Request $req)
    // {
    //     $validated = Validator::make(
    //         $req->all(),
    //         [
    //             'email' => 'required|email',
    //             'password' => 'required',
    //             'type' => 'nullable|in:mobile',
    //             'moduleId' => 'nullable|int'
    //         ]
    //     );
    //     if ($validated->fails()) {
    //         return validationError($validated);
    //     }

    //     try {
    //         $mWfRoleusermap = new WfRoleusermap();
    //         $mUlbMaster = new UlbMaster();
    //         $user = $this->_mUser->getUserByEmail($req->email);
    //         if (!$user) {
    //             throw new Exception('Invalid Credentials');
    //         }
    //         if ($user->suspended == true) {
    //             throw new Exception('You are not authorized to log in!');
    //         }

    //         $checkUlbStatus = $mUlbMaster->checkUlb($user);
    //         if (!$checkUlbStatus) {
    //             throw new Exception('This ULB is restricted by SuperAdmin!');
    //         }

    //         if ($req->moduleId) {
    //             $checkModule = $this->_UlbModulePermission->check($user, $req);
    //             if (!$checkModule) {
    //                 throw new Exception('Module is restricted for this ULB!');
    //             }
    //         }

    //         if (Hash::check($req->password, $user->password)) {
    //             $token = $user->createToken('my-app-token')->plainTextToken;

    //             // Set last activity in Redis
    //             $citizenUserType = Config::get('workflow-constants.USER_TYPES.1');
    //             $redisKey = $user->user_type == $citizenUserType
    //                 ? 'last_activity_citizen_' . $user->id
    //                 : 'last_activity_' . $user->id;
    //             Redis::set($redisKey, Carbon::now()->toDateTimeString());
    //             // \log()::info('Set Redis key on login: ' . $redisKey); // Debug log

    //             $menuRoleDetails = $mWfRoleusermap->getRoleDetailsByUserId($user->id);
    //             $role = collect($menuRoleDetails)->map(function ($value) {
    //                 return $value['roles'];
    //             });
    //             $roleId = collect($menuRoleDetails)->map(function ($value) {
    //                 return $value['roleId'];
    //             });

    //             if (!$req->type && $this->checkMobileUserRole($menuRoleDetails)) {
    //                 throw new Exception('Mobile user cannot log in as web user');
    //             }

    //             $jeRole = collect($menuRoleDetails)->where('roles', 'JUNIOR ENGINEER');
    //             if ($jeRole->isEmpty() && $req->type && !$this->checkMobileUserRole($menuRoleDetails)) {
    //                 throw new Exception('Web user cannot log in as mobile user');
    //             }

    //             if (in_array($user->user_type, ['TC', 'TL'])) {
    //                 $userlog = new UserLoginDetail();
    //                 $userlog->user_id = $user->id;
    //                 $userlog->login_date = Carbon::now()->format('Y-m-d');
    //                 $userlog->login_time = Carbon::now()->format('h:i:s a');
    //                 $userlog->ip_address = $req->ip();
    //                 $userlog->save();
    //             }

    //             $user->ulbName = UlbMaster::find($user->ulb_id)->ulb_name ?? '';
    //             $data = [
    //                 'token' => $token,
    //                 'userDetails' => $user,
    //                 'userDetails.role' => $role,
    //                 'userDetails.roleId' => $roleId,
    //             ];

    //             return responseMsgs(true, 'You have logged in successfully', $data, '010101', '1.0', responseTime(), 'POST', $req->deviceId);
    //         }

    //         throw new Exception('Invalid Credentials');
    //     } catch (Exception $e) {
    //         return responseMsg(false, $e->getMessage(), '');
    //     }
    // }

    // public function encrypted(Request $req)
    // {
    //     try {
    //         $secretKey = Config::get('constants.SECRETKEY');

    //         $method = "AES-256-CBC";
    //         $key = hash('sha256', $secretKey, true);
    //         $iv = substr(hash('sha256', $secretKey), 0, 16);

    //         $plainText = "Admin1@";

    //         $encrypted = openssl_encrypt($plainText, $method, $key, OPENSSL_RAW_DATA, $iv);
    //         $encryptedBase64 = base64_encode($encrypted);
    //         return responseMsgs(true, "Data Retrieved", $encryptedBase64, "120503", "01", responseTime(), $req->getMethod(), $req->deviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "120503", "01", responseTime(), $req->getMethod(), $req->deviceId);
    //     }
    // }

    /**
     * |
     */
    // OLD Changing Password
    // public function changePass(ChangePassRequest $request)
    // {
    //     try {
    //         $id = auth()->user()->id;
    //         $user = User::find($id);
    //         $validPassword = Hash::check($request->password, $user->password);
    //         if ($validPassword) {

    //             $user->password = Hash::make($request->newPassword);
    //             $user->save();
    //             $token = $request->user()->currentAccessToken();
    //             $token->expires_at = Carbon::now();
    //             $token->save();
    //             return responseMsgs(true, 'Successfully Changed the Password', "", "", "02", responseTime(), "POST", $request->deviceId);
    //         }
    //         throw new Exception("Old Password dosen't Match!");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "", "02", responseTime(), "POST", $request->deviceId);
    //     }
    // }


    /***
     * |captcha generation
     */
    // public function getCaptcha()
    // {
    //     $secretKey = "c2ec6f788fb85720bf48c8cc7c2db572596c585a15df18583e1234f147b1c2897aad12e7bebbc4c03c765d0e878427ba6370439d38f39340d7e";
    //     // Generate 6-character alphanumeric code
    //     $captchaCode = strtoupper(Str::random(6)); // e.g., "A1B2C3"

    //     $captchaId = Str::uuid()->toString();

    //     // Store in Redis for 5 minutes (300 seconds)
    //     Redis::setex("CAPTCHA:$captchaId", 300, $captchaCode);

    //     return response()->json([
    //         'captcha_id' => $captchaId,
    //         'captcha_code' => $captchaCode, // For testing; in production display in UI
    //     ]);
    // }

    public function getCaptcha()
    {
        $secretKey = Config::get('constants.SECRETKEY');

        // Generate 6-character alphanumeric code
        $captchaCode = strtoupper(Str::random(6)); // Example: "A1B2C3"

        $captchaId = Str::uuid()->toString();

        // Store plain captcha in Redis for 5 minutes
        Redis::setex("CAPTCHA:$captchaId", 300, $captchaCode);

        // Encrypt captcha before sending to frontend
        $method = 'AES-256-CBC';
        $key = hash('sha256', $secretKey, true);
        $iv = substr(hash('sha256', $secretKey), 0, 16);

        $encryptedCaptcha = base64_encode(openssl_encrypt($captchaCode, $method, $key, OPENSSL_RAW_DATA, $iv));
        $data = [
            'captcha_id' => $captchaId,
            'captcha_code' => $encryptedCaptcha, // ✅ send encrypted version
        ];
        return responseMsgs(true, "CAPTCHA", $data, 010101, "1.0", responseTime(), "POST", "");
    }

    //
    // $captchaModules = Config::get('constants.MODULES_WITH_CAPTCHA', []);
    // if (in_array($req->moduleId, $captchaModules)) {
    //     $storedCode = Redis::get("CAPTCHA:{$req->captcha_id}");
    //     if (!$storedCode || $storedCode != $req->captcha_code) {
    //         throw new Exception("Invalid or expired captcha code");
    //     }

    //     // Remove used captcha
    //     Redis::del("CAPTCHA:{$req->captcha_id}");
    // }
    // ✅ Rate Limiting: Allow 5 attempts per 120 seconds per IP

    public function userListById(Request $req)
    {
        $req->validate([
            'id' => 'required'
        ]);

        try {
            $perPage = $req->perPage ?? 100;
            $id = $req->id;

            if ($id == null) {
                throw new Exception('Please Provide Ulb Id!');
            }

            // Base query
            $query = User::select(
                'id',
                'user_name',
                'mobile',
                'email',
                'user_type',
                'name',
                'address',
                'alternate_mobile',
                'suspended',
                'reference_no',
                DB::raw("CONCAT(photo_relative_path, '/', photo) AS photo"),
                DB::raw("CONCAT(sign_relative_path, '/', signature) AS signature")
            )
                ->where('users.id', $id)
                ->orderBy('users.id', 'desc');

            // Apply pipeline filters
            $filteredQuery = app(Pipeline::class)
                ->send($query)
                ->through([
                    SearchByName::class,
                    SearchByEmail::class,
                    SearchByMobile::class,
                    SearchByRole::class
                ])
                ->thenReturn();

            // Paginate results
            $userList = $filteredQuery->paginate($perPage);

            // Attach document data to each user
            $docUpload = new DocUpload();
            $data = $userList->map(function ($user) use ($docUpload) {
                $docDetails = $docUpload->getSingleDocUrl($user); // Fetch document details
                $docUrl = $docDetails['doc_path'] ?? null;

                return [
                    'id'             => $user->id,
                    'user_name'      => $user->user_name,
                    'mobile'         => $user->mobile,
                    'alternateMobile' => $user->alternate_mobile,
                    'email'          => $user->email,
                    'name'           => $user->name,
                    'user_type'      => $user->user_type,
                    'address'        => $user->address,
                    'suspended'      => $user->suspended,
                    'referenceNo'    => $user->reference_no,
                    'photo'          => $user->photo,
                    'signature'      => $user->signature,
                    'documentUrl'    => $docUrl, // Add document URL to response
                ];
            });

            // Build response structure
            $userListResponse = [
                "current_page" => $userList->currentPage(),
                "last_page"    => $userList->lastPage(),
                "data"         => $data,
                "total"        => $userList->total(),
            ];

            return responseMsgs(true, "User List", $userListResponse, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    public function updateUserName(Request $req)
    {
        try {
            $validated = Validator::make($req->all(), [
                'id'        => 'required|exists:users,id',
                'user_name' => 'nullable|string',
                'emp_id'    => 'nullable|string',
            ]);

            if ($validated->fails()) {
                return responseMsgs(false,$validated->errors()->first(),"", "USR009","01",responseTime(),$req->getMethod(),$req->deviceId);
            }

            $user = User::find($req->id);

            if (!$user) {
                return responseMsgs(false, "User not found", "", "USR009", "01", responseTime(), $req->getMethod(), $req->deviceId);
            }

            // Update only provided fields
            if ($req->filled('user_name')) {
                $user->user_name = $req->user_name;
            }

            if ($req->filled('emp_id')) {
                $user->emp_id = $req->emp_id;
            }

            if (!$req->filled('user_name') && !$req->filled('emp_id')) {
                return responseMsgs(
                    false,
                    "Nothing to update",
                    "",
                    "USR009",
                    "01",
                    responseTime(),
                    $req->getMethod(),
                    $req->deviceId
                );
            }

            $user->save();

            return responseMsgs(
                true,
                "User details updated successfully",
                $user,
                "USR009",
                "01",
                responseTime(),
                $req->getMethod(),
                $req->deviceId
            );

        } catch (Exception $e) {
            return responseMsgs(
                false,
                $e->getMessage(),
                "",
                "USR009",
                "01",
                responseTime(),
                $req->getMethod(),
                $req->deviceId
            );
            
        }
    }


    /**
     * | Bulk Update Password for All Users
     */
    public function bulkUpdatePassword(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                'customPassword' => 'required|string|min:6'
            ]
        );
        
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            DB::beginTransaction();
            
            $hashedPassword = Hash::make($request->customPassword);
            $updatedCount = User::query()->update(['password' => $hashedPassword]);
            
            DB::commit();
            
            return responseMsgs(true, "Password updated for {$updatedCount} users", ['count' => $updatedCount], "", "01", responseTime(), "POST", $request->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", $request->deviceId ?? "");
        }
    }

}
