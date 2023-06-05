<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthorizeRequestUser;
use App\Http\Requests\Auth\AuthUserRequest;
use App\Http\Requests\Auth\ChangePassRequest;
use App\Http\Requests\Auth\OtpChangePass;
use App\Models\User;
use App\Traits\Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use Auth;
    private $_mUser;
    public function __construct()
    {
        $this->_mUser = new User();
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
        if ($validated->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'validation error',
                'errors'  => $validated->errors()
            ]);
        }
        try {
            $user = $this->_mUser->getUserByEmail($req->email);
            if ($user->suspended == true)
                throw new Exception("You are not authorized to log in!");
            if (Hash::check($req->password, $user->password)) {
                $data['token'] = $user->createToken('my-app-token')->plainTextToken;
                $data['userDetails'] = $user;
                return responseMsgs(true, "You have Logged In Successfully", $data, 010101, "1.0", responseTime(), "POST", $req->deviceId);
            }

            throw new Exception("Password Not Matched");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
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
     * | User Store
     */
    public function store(AuthUserRequest $request)
    {
        try {
            // Validation---@source-App\Http\Requests\AuthUserRequest
            $user = new User;
            $this->saving($user, $request);                     // Storing data using Auth trait
            $user->password = Hash::make($request->password);
            $user->save();
            return responseMsg(true, "User Registered Successfully !! Please Continue to Login", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    // Store the user in database from Authority
    public function authorizeStore(AuthorizeRequestUser $request)
    {
        try {
            $request['ulb'] = auth()->user()->ulb_id;
            $user = new User;
            $this->saving($user, $request);                     // Storing data using Auth trait
            $user->password = Hash::make($request->password);
            $user->save();
            return responseMsg(true, "User Registered Successfully !! Please Continue to Login", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
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

                return responseMsgs(true, 'Successfully Changed the Password', "", "", "02", ".ms", "POST", $request->deviceId);
            }
            throw new Exception("Old Password dosen't Match!");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "02", ".ms", "POST", $request->deviceId);
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

            return response()->json(['Status' => 'True', 'Message' => 'Successfully Changed the Password'], 200);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", ".ms", "POST", $request->deviceId);
        }
    }

    /**
     * | For Showing Logged In User Details 
     * | #user_id= Get the id of current user 
     * | #redis= Find the details On Redis Server
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

            return responseMsgs(true, "Data Fetched", $usersDetails, "", "01", ".ms", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", ".ms", "POST", "");
        }
    }

    /**
     * | Edit My Profile
     */
    public function editMyProfile(Request $request)
    {
        $user = authUser();
        $meta['id'] = $user->id;
        $meta['ulb'] = $user->ulb_id;
        $request->request->add($meta);
        return $this->update($request);
    }

    /**
     * | Update User Details
     */
    public function update(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            [
                "id" => 'required',
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255']
            ]
        );
        if ($validated->fails()) {
            return validationError($validated);
        }

        try {
            $id = $request->id;
            $user = User::find($id);
            $stmt = $user->email == $request->email;
            if ($stmt) {
                $this->saving($user, $request);
                $this->savingExtras($user, $request);
                $user->save();
            }
            if (!$stmt) {
                $check = User::where('email', '=', $request->email)->first();
                if ($check) {
                    throw new Exception('Email Is Already Existing');
                }
                if (!$check) {
                    $this->saving($user, $request);
                    $this->savingExtras($user, $request);
                    $user->save();
                }
            }
            return responseMsgs(true, "Successfully Updated", "", "", "01", ".ms", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", ".ms", "POST", "");
        }
    }

    /**
     * | Delete User
     */
    public function deleteUser(Request $request)
    {
        $data = User::find($request->id);
        $data->suspended = true;
        $data->save();
        return responseMsg(true, "Data Deleted", '');
    }

    /**
     * | Get Users Details by Id
     */
    public function getUser(Request $request)
    {
        try {
            $mUser = new User();
            return $mUser->getUserRoleDtls()
                ->where('users.id', $request->id)
                ->first();
            return responseMsgs(true, "Successfully Updated", "", "", "01", ".ms", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", ".ms", "POST", "");
        }
    }
}
