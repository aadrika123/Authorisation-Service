<?php

namespace App\Traits;

use App\Models\Menu\WfRolemenu;
use App\Models\User;
use App\Models\Workflows\WfRoleusermap;
use App\Repository\Menu\Concrete\MenuRepo;
use Illuminate\Http\Request;
use App\MicroServices\DocUpload;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Razorpay\Api\Collection;

/**
 * Trait for saving and editing the Users and Citizen register also
 * Created for reducing the duplication for the Saving and editing codes
 * --------------------------------------------------------------------------------------------------------
 * Created by-Anshu Kumar
 * Updated by-Sam kerketta
 * Created On-16-07-2022 
 * --------------------------------------------------------------------------------------------------------
 */

trait Auth
{

    /**
     * Saving User Credentials 
     */
    public function saving($user, $request)
    {
        $docUpload = new DocUpload;
        $imageRelativePath = 'Uploads/User/Photo';
        $signatureRelativePath = 'Uploads/User/Signature';
        $user->name = $request->name;
        $user->mobile = $request->mobile;
        $user->email = $request->email;
        $user->alternate_mobile = $request->altMobile;
        $user->address = $request->address;
        $user->asset_type_id = is_array($request->assetTypeId) ? '{' . implode(',', $request->assetTypeId) . '}' : ($request->assetTypeId ? '{' . $request->assetTypeId . '}' : null);           // Added only for LAMS module
        $user->ulb_id = $request->ulbId ?? authUser()->ulb_id;
        if ($request->userType) {
            $user->user_type = $request->userType;
        }
        if ($request->description) {
            $user->description = $request->description;
        }
        if ($request->workflowParticipant) {
            $user->workflow_participant = $request->workflowParticipant;
        }
        if ($request->photo) {
            $document = $request->photo;
            $newRequest = new Request(['document' => $document]);

            $response = $docUpload->checkDoc($newRequest);
            
            if (is_object($response) && method_exists($response, 'getContent')) {
                $responseData = json_decode($response->getContent(), true);
            } elseif (is_array($response)) {
                $responseData = $response;
            } else {
                $responseData = null;
            }
            
            if (isset($responseData['data']['uniqueId'])) {
                $user->unique_id = $responseData['data']['uniqueId'];
                $user->reference_no = $responseData['data']['ReferenceNo'];
            }
        }
        if ($request->signature) {
            $document = $request->signature;
            $newRequest = new Request(['document' => $document]);

            $response = $docUpload->checkDoc($newRequest);
            
            if (is_object($response) && method_exists($response, 'getContent')) {
                $responseData = json_decode($response->getContent(), true);
            } elseif (is_array($response)) {
                $responseData = $response;
            } else {
                $responseData = null;
            }
            
            if (isset($responseData['data']['uniqueId'])) {
                $user->unique_id = $responseData['data']['uniqueId'];
                $user->reference_no = $responseData['data']['ReferenceNo'];
            }
        }

        $token = Str::random(80);                       //Generating Random Token for Initial
        $user->remember_token = $token;
    }

    /**
     * Saving Extra User Credentials
     */
    public function savingExtras($user, $request)
    {
        if ($request->suspended) {
            $user->suspended = $request->suspended;
        }
        if ($request->superUser) {
            $user->super_user = $request->superUser;
        }
    }
    /**
     * Saving Extra User Credentials
     */
    public function updateClientId($user, $request)
    {
        if ($request->clientId) {
            $user->client_id = $request->clientId;
        }
    }
}
