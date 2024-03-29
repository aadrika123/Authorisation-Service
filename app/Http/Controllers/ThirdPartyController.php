<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestSendOtp;
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
use App\Pipelines\User\SearchByEmail;
use App\Pipelines\User\SearchByMobile;
use App\Pipelines\Citizen\CitizenSearchByEmail;
use App\Pipelines\Citizen\CitizenSearchByMobile;
use Illuminate\Pipeline\Pipeline;

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
            $mOtpRequest = new OtpRequest();
            if ($request->type == "Register") {
                $userDetails = ActiveCitizen::where('mobile', $request->mobileNo)
                    ->first();
                if ($userDetails) {
                    throw new Exception("Mobile no $request->mobileNo is registered to An existing account!");
                }
            }
            if ($request->type == "Forgot") {
                $userDetails = ActiveCitizen::where('mobile', $request->mobileNo)
                    ->first();
                if (!$userDetails) {
                    throw new Exception("Pleas check your mobile.no!");
                }
            }
            $generateOtp = $this->generateOtp();
            DB::beginTransaction();
            $mOtpRequest->saveOtp($request, $generateOtp);
            DB::commit();
            return responseMsgs(true, "OTP send to your mobile No!", $generateOtp, "", "01", ".ms", "POST", "");
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "0101", "01", ".ms", "POST", "");
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
        // $otp = Carbon::createFromDate()->milli . random_int(100, 999);
        $otp = 123123;
        return $otp;
    }

    /**
     * | Forgot Password Via Otp or Mail
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
            }
            elseif(!$userData && $mobileNo)
            {
                throw new Exception("Mobile doesn't exist");
            }
            elseif(!$userData)
            {
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

}
