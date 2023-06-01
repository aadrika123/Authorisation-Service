<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
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
                $token = $user->createToken('my-app-token')->plainTextToken;
                return responseMsgs(true, "You have Logged In Successfully", $token, 010101, "1.0", responseTime(), "POST", $req->deviceId);
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
}
