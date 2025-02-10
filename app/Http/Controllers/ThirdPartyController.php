<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestSendOtp;
use App\Http\Requests\RequestVerifyOtp;
use App\MicroServices\IdGeneration;
use App\Models\Auth\ActiveCitizen;
use App\Models\OtpMaster;
use App\Models\OtpRequest;
use Seshac\Otp\Otp;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use WpOrg\Requests\Auth;
use App\Models\Auth\User;
use App\Models\PasswordResetOtpToken;
use App\Pipelines\User\SearchByEmail;
use App\Pipelines\User\SearchByMobile;
use App\Pipelines\Citizen\CitizenSearchByEmail;
use App\Pipelines\Citizen\CitizenSearchByMobile;
use App\Pipelines\Otp\SearchByEmail as OtpSearchByEmail;
use App\Pipelines\Otp\SearchByMobile as OtpSearchByMobile;
use App\Pipelines\Otp\SearchByOtpType as OtpSearchByOtpType;
use App\Pipelines\Otp\SearchByUserType as OtpSearchByUserType;
use App\Pipelines\Otp\SearchByOtp as OtpSearchByOtp;
use Carbon\Carbon;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ThirdPartyController extends Controller
{
    // OTP related Operations


    /**
     * | Send OTP for Use
     * | OTP for Changing PassWord using the mobile no 
     * | @param request
     * | @var 
     * | @return 
        | Serial No : 01
        | Working
        | Dont share otp 
     */
    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'mobileNo' => "required|digits:10|regex:/[0-9]{10}/", #exists:active_citizens,mobile|
                'type' => "nullable|in:Register,Forgot",
            ]);
            $type = "";
            $mOtpRequest = new OtpRequest();
            if ($request->type == "Register") {
                $type = "Citizen Registration";
                $userDetails = ActiveCitizen::where('mobile', $request->mobileNo)
                    ->first();
                if ($userDetails) {
                    throw new Exception("Mobile no $request->mobileNo is registered to An existing account!");
                }
            }
            if ($request->type == "Forgot") {
                $type = "Forgot Password";
                $userDetails = ActiveCitizen::where('mobile', $request->mobileNo)
                    ->first();
                if (!$userDetails) {
                    throw new Exception("Pleas check your mobile.no!");
                }
            }
            $generateOtp = $this->generateOtp();
            DB::beginTransaction();
            $mOtpRequest->saveOtp($request, $generateOtp);

            $mobile     = $request->mobileNo;
            $message    = "OTP for $type of UD&HD is $generateOtp. This OTP is valid for 10 minutes. For more info call us 1800123123.-UD&HD, GOJ";
            $templateid = "1307171162976397795";
            $data       = send_sms($mobile, $message, $templateid);

            // $whatsaapData = (Whatsapp_Send(
            //     $request->mobileNo,
            //     "send_sms",
            //     [
            //         "content_type" => "text",
            //         [
            //             $type,
            //             $generateOtp
            //         ]
            //     ]
            // ));
            DB::commit();
            return responseMsgs(true, "OTP send to your mobile No!", $data, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "0101", "01", responseTime(), "POST", "");
        }
    }

    /**
     * | Verify OTP 
     * | Check OTP and Create a Token
     * | @param request
        | Serial No : 02
        | Working
     */
    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'otp' => "required|digits:6",
                'mobileNo' => "required|digits:10|regex:/[0-9]{10}/|exists:otp_requests,mobile_no"
            ]);
            # model
            $mOtpMaster     = new OtpRequest();
            $mActiveCitizen = new ActiveCitizen();

            # logi 
            DB::beginTransaction();
            $checkOtp = $mOtpMaster->checkOtp($request);
            if (!$checkOtp) {
                $msg = "OTP not match!";
                return responseMsgs(false, $msg, "", "", "01", ".ms", "POST", "");
            }
            $token = $mActiveCitizen->changeToken($request);
            $token = collect($token)->first();

            $checkOtp->delete();
            DB::commit();
            return responseMsgs(true, "OTP Validated!", remove_null($token), "", "01", ".ms", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "", "01", ".ms", "POST", "");
        }
    }

    /**
     * | Generate Random OTP 
     */
    public function generateOtp()
    {
        // $otp = str_pad(Carbon::createFromDate()->milli . random_int(100, 999), 6, 0);
        $otp = 123123;
        return $otp;
    }

    /**
     * | Forgot Password Via Otp or Mail
     * | Created By: Mrinal Kumar
     * | Date      : 29-03-2024
         Email And Sms is commented for now
     */
    public function forgotPasswordViaOtp(RequestSendOtp $request)
    {
        try {
            $mOtpRequest = new OtpRequest();
            $email = $request->email;
            $mobileNo  = $request->mobile;
            $userType = $request->userType;
            $otpType = 'Forgot Password';
            $request->merge(["strict" => true]);
            if (!$email && !$mobileNo) {
                throw new Exception("Invalid Data Given");
            }
            $userData = User::where('suspended', false)
                ->orderByDesc('id');
            $userData = app(Pipeline::class)
                ->send(
                    $userData
                )
                ->through([
                    SearchByEmail::class,
                    SearchByMobile::class
                ])
                ->thenReturn()
                ->first();
            if ($userType == "Citizen") {
                $userData = ActiveCitizen::orderByDesc('id');
                $userData = app(Pipeline::class)
                    ->send(
                        $userData
                    )
                    ->through([
                        CitizenSearchByEmail::class,
                        CitizenSearchByMobile::class
                    ])
                    ->thenReturn()
                    ->first();
            }
            if (!$userData && $email) {
                throw new Exception("Email doesn't exist");
            } elseif (!$userData && $mobileNo) {
                throw new Exception("Mobile doesn't exist");
            } elseif (!$userData) {
                throw new Exception("Data Not Found");
            }
            $generateOtp = $this->generateOtp();
            $request->merge([
                "mobileNo" => $request->mobile,
                "type" => $otpType,
                "otpType" => $otpType,
                "Otp" => $generateOtp,
                "userId" => $userData->id,
                "userType" => $userData->gettable(),
            ]);
            // $smsDta = OTP($request->all());
            // if ($mobileNo &&  !$smsDta["status"]) {
            //     throw new Exception("Some Error Occurs Server On Otp Sending");
            // }
            $sms = $smsDta["sms"] ?? "";
            $temp_id = $smsDta["temp_id"] ?? "";
            $sendsOn = [];
            // if ($mobileNo) {
            //     $response = send_sms($mobileNo, $sms, $temp_id);
            //     $sendsOn[] = "Mobile No.";
            // }
            if ($email) {
                $sendsOn[] = "Email";
                $details = [
                    "title" => "Password Reset Information",
                    "name"  => $userData->getTable() != "users" ? $userData->user_name : $userData->name,
                    "Otp" => $request->Otp
                ];
                // try {
                //     Mail::to($userData->email)->send(new ForgotPassword($details));
                // } catch (Exception $e) {
                //     throw new Exception("Currently Email Service is stopped Please try another way");
                // }
            }
            $responseSms = "";
            foreach ($sendsOn as $val) {
                $responseSms .= ($val . " & ");
            }
            $responseSms = trim($responseSms, "& ");
            // $responseSms = "OTP send to your " . $responseSms;
            $responseSms = "Your OTP Is 123123";
            $mOtpRequest->saveOtp($request, $generateOtp);

            return responseMsgs(true, $responseSms, "", "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    /**
     * | Verify Otp Version 2
     */
    public function otpVerification(RequestVerifyOtp $request)
    {
        try {
            $mOtpMaster             = new OtpRequest();
            $mPasswordResetOtpToken = new PasswordResetOtpToken();

            $checkOtp = $mOtpMaster::orderByDesc("id");
            $checkOtp = app(Pipeline::class)
                ->send(
                    $checkOtp
                )
                ->through([
                    OtpSearchByEmail::class,
                    OtpSearchByMobile::class,
                    OtpSearchByOtpType::class,
                    OtpSearchByUserType::class,
                    OtpSearchByOtp::class,
                ])
                ->thenReturn()
                ->first();

            if (!$checkOtp) {
                throw new Exception("OTP not match!");
            }
            if ($checkOtp->expires_at < Carbon::now()) {
                $this->transerLog($checkOtp);
                throw new Exception("OTP is expired");
            }
            $checkOtp->use_date_time = Carbon::now();
            $request->merge([
                "tokenableType"  => $checkOtp->gettable(),
                "tokenableId"  => $checkOtp->id,
                "userType"     => $checkOtp->user_type,
                "userId"     => $checkOtp->user_id,
            ]);

            DB::beginTransaction();
            $checkOtp->update();
            $this->transerLog($checkOtp);

            $sms = "OTP Validated!";
            $response = [];
            if ($checkOtp->otp_type == "Forgot Password") {
                $sms = "Proceed For Password Update. Token Is Valid Only for 10 minutes";
                $response["token"] = $mPasswordResetOtpToken->store($request);
            }
            DB::commit();

            return responseMsgs(true, $sms, $response, "", "01", responseTime(), "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "", "01", responseTime(), "POST", "");
        }
    }

    private function transerLog(OtpRequest $checkOtp)
    {
        $OldOtps =  OtpRequest::where("expires_at", Carbon::now())
            ->whereNotNull("expires_at")
            ->where(DB::raw("CAST(created_at AS Date)"), Carbon::now()->format("Y-m-d"))
            ->get();
        foreach ($OldOtps as $val) {
            $otpLog = $val->replicate();
            $otpLog->setTable('log_otp_requests');
            $otpLog->id = $val->id;
            $otpLog->save();
            $checkOtp->delete();
        }
        if ($checkOtp) {
            $otpLog = $checkOtp->replicate();
            $otpLog->setTable('log_otp_requests');
            $otpLog->id = $checkOtp->id;
            $otpLog->save();
            $checkOtp->delete();
        }
    }

    /**
     * | Password Change Via Token
     */
    public function changePasswordViaToken(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                "token" => "required",
                'password' => [
                    'required',
                    'min:6',
                    'max:255',
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/'  // must contain a special character
                ]
            ]
        );
        if ($validator->fails())
            return validationError($validator);

        try {
            $mActiveCitizen = new ActiveCitizen();
            $mUsers         = new User();
            $mPasswordResetOtpToken         = new PasswordResetOtpToken();
            $requestToken = $mPasswordResetOtpToken
                ->where("token", $request->token)
                ->where("status", 0)
                ->whereNotNull("user_type")
                ->whereNotNull("user_id")
                ->first();
            if (!$requestToken) {
                throw new Exception("Invalid Token");
            }
            if ($requestToken->expires_at < Carbon::now()) {
                throw new Exception("Token Is Expired");
            }
            $users = $requestToken->user_type == $mActiveCitizen->gettable() ? $mActiveCitizen->find($requestToken->user_id) : $mUsers->find($requestToken->user_id);
            if (!$users || (!in_array($requestToken->user_type, [$mActiveCitizen->gettable(), $mUsers->gettable()]))) {
                throw new Exception("Invalid Password Update Request Apply");
            }
            $requestToken->status = 1;
            $users->password = Hash::make($request->password);

            DB::beginTransaction();
            $users->tokens->each(function ($token, $key) {
                $token->expires_at = Carbon::now();
                $token->update();
                $token->delete();
            });
            $requestToken->update();
            $users->update();
            DB::commit();

            return responseMsgs(true, "Password Updated Successfully", "", "", "01", ".ms", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "", "01", ".ms", "POST", "");
        }
    }


    public function changesPasswordByDev(Request $request)
    {
        // Validate request
        $validator = Validator::make(
            $request->all(),
            [
                "ulbId" => "required",
                "email"  => "required|email",
                'password' => [
                    'required',
                    'min:6',
                    'max:255',
                    'regex:/[a-z]/',      // At least one lowercase letter
                    'regex:/[A-Z]/',      // At least one uppercase letter
                    'regex:/[0-9]/',      // At least one digit
                    'regex:/[@$!%*#?&]/'  // At least one special character
                ]
            ]
        );

        if ($validator->fails()) {
            return validationError($validator);
        }

        try {
            $mUsers = new User();

            // Fetch user by email
            $userDetails = $mUsers->getUserByEmailUlb($request->email, $request->ulbId);
            if (!$userDetails) {
                throw new Exception("User Not Found");
            }

            DB::beginTransaction();

            // Update user password
            $userDetails->password = Hash::make($request->password);
            $userDetails->save();

            // Revoke all existing tokens
            if (method_exists($userDetails, 'tokens')) {
                $userDetails->tokens()->delete();
            }

            DB::commit();

            return responseMsgs(true, "Password Updated Successfully", "", "", "01", ".ms", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "", "01", ".ms", "POST", "");
        }
    }
}
