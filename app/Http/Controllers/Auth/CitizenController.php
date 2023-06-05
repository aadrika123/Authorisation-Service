<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\MicroServices\DocUpload;
use App\Models\Auth\ActiveCitizen;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CitizenController extends Controller
{

    /**
     * | Citizen Register
     */
    public function citizenRegister(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'mobile'   => 'required|numeric|digits:10',
            'password' => [
                'required',
                'min:6',
                'max:255',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/'  // must contain a special character
            ],
        ]);

        try {

            DB::beginTransaction();
            $mCitizen = new ActiveCitizen();
            $citizens = $mCitizen->getCitizenByMobile($request->mobile);
            if (isset($citizens))
                return responseMsgs(false, "This Mobile No is Already Existing", "");

            $id = $mCitizen->citizenRegister($mCitizen, $request);        //Citizen save in model

            $this->docUpload($request, $id);

            DB::commit();

            return responseMsg(true, "Succesfully Registered", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
            DB::rollBack();
        }
    }

    /**
     * | Doc upload
     */
    public function docUpload($request, $id)
    {
        $docUpload = new DocUpload;
        $imageRelativePath = 'Uploads/Citizen/' . $id;
        ActiveCitizen::where('id', $id)
            ->update([
                'relative_path' => $imageRelativePath . '/',
            ]);

        if ($request->photo) {
            $filename = 'photo';
            $document = $request->photo;
            $imageName = $docUpload->upload($filename, $document, $imageRelativePath);

            ActiveCitizen::where('id', $id)
                ->update([
                    'profile_photo' => $imageName,
                ]);
        }

        if ($request->aadharDoc) {
            $filename = 'aadharDoc';
            $document = $request->aadharDoc;
            $imageName = $docUpload->upload($filename, $document, $imageRelativePath);

            ActiveCitizen::where('id', $id)
                ->update([
                    'aadhar_doc' => $imageName,
                ]);
        }

        if ($request->speciallyAbledDoc) {
            $filename = 'speciallyAbled';
            $document = $request->speciallyAbledDoc;
            $imageName = $docUpload->upload($filename, $document, $imageRelativePath);

            ActiveCitizen::where('id', $id)
                ->update([
                    'specially_abled_doc' => $imageName,
                ]);
        }

        if ($request->armedForceDoc) {
            $filename = 'armedForce';
            $document = $request->armedForceDoc;
            $imageName = $docUpload->upload($filename, $document, $imageRelativePath);

            ActiveCitizen::where('id', $id)
                ->update([
                    'armed_force_doc' => $imageName,
                ]);
        }
    }

    /**
     *  Citizen Login
     */
    public function citizenLogin(Request $req)
    {
        try {
            $req->validate([
                'mobile' => "required",
                'password' => [
                    'required',
                    'min:6',
                    'max:255',
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/'  // must contain a special character
                ],
            ]);
            $citizenInfo = ActiveCitizen::where('mobile', $req->mobile)
                ->first();
            if (!$citizenInfo) {
                $msg = "Oops! Given mobile no does not exist";
                return responseMsg(false, $msg, "");
            }

            $userDetails['id'] = $citizenInfo->id;
            $userDetails['userName'] = $citizenInfo->user_name;
            $userDetails['mobile'] = $citizenInfo->mobile;
            $userDetails['userType'] = $citizenInfo->user_type;
            $userDetails['user_type'] = $citizenInfo->user_type;
            $userDetails['ip_address'] = $citizenInfo->ip_address;

            if ($citizenInfo) {
                if (Hash::check($req->password, $citizenInfo->password)) {
                    $token = $citizenInfo->createToken('my-app-token')->plainTextToken;
                    $citizenInfo->remember_token = $token;
                    $citizenInfo->save();
                    $userDetails['token'] = $token;
                    $key = 'last_activity_citizen_' . $citizenInfo->id;               // Set last activity key 
                    return responseMsgs(true, 'You r logged in now', $userDetails, '', "1.0", "494ms", "POST", "");
                } else {
                    $msg = "Incorrect Password";
                    return responseMsg(false, $msg, '');
                }
            }
        }
        // Authentication Using Sql Database
        catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Citizen Logout 
     */
    public function citizenLogout(Request $req)
    {
        // token();
        $id =  auth()->user()->id;

        $user = ActiveCitizen::where('id', $id)->first();
        $user->remember_token = null;
        $user->save();

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}
