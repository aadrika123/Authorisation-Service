<?php

namespace App\Http\Controllers;

use App\Models\ActionMaster;
use App\Models\Auth\ActiveCitizen;
use App\Models\EpramaanLogin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\File;

/**
 ** Use following packages for E-parmaan
 **/

use Jose\Component\Core\JWK;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\JWEDecrypterFactory;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256KW;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\JWELoader;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Signature\Serializer\CompactSerializer as SignatureCompactSerializer;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\KeyManagement\JWKFactory;
use RuntimeException;

class EpramaanController extends Controller
{

    /**
     * | Login with e-pramaan
     */
    public function loginEpramaan(Request $req)
    {
        setcookie("verifier_c", "", time() - 3600, "/");
        setcookie("nonce_c", "", time() - 3600, "/");
        $type  = $req->type;
        // $serviceId    = '100001031';
        // $redirect_uri = 'http://site2.aadrikainfomedia.in/citizen/authResponseConsumer.do'; //it is working

        switch ($type) {
            case 'citizen':
                $serviceId    = '100001033';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/citizen/login/e-pramaan';
                break;

            case 'property':
                $serviceId    = '100001034';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/property/login/e-pramaan';
                break;

            case 'water':
                $serviceId    = '100001035';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/water/login/e-pramaan';
                break;

            case 'trade':
                $serviceId    = '100001036';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/trade/login/e-pramaan';
                break;

            case 'advertisement':
                $serviceId    = '100001037';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/advertisement/login/e-pramaan';
                break;

            case 'pet':
                $serviceId    = '100001038';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/pet/login/e-pramaan';
                break;

            case 'marriage':
                $serviceId    = '100001039';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/marriage/login/e-pramaan';
                break;

            case 'agency':
                $serviceId    = '100001041';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/agency/login/e-pramaan';
                break;

            default:
                $serviceId    = '100001033';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/citizen/login/e-pramaan';
                break;
        }

        $scope                 = 'openid';
        $response_type         = 'code';
        $code_challenge_method = 'S256';
        $aeskey                = 'fddbb838-b6b1-44c4-93b3-dc9ee91f174a';
        $url                   = 'https://epstg.meripehchaan.gov.in/openid/jwt/processJwtAuthGrantRequest.do';
        $request_uri           = 'https://epstg.meripehchaan.gov.in/openid/jwt/processJwtAuthGrantRequest.do';

        $state = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));

        //nonce
        $nonce = bin2hex(random_bytes(16));

        setcookie("nonce_c", "$nonce", time() + 3600, "/");

        //verifier
        $verifier_bytes = random_bytes(64);
        $code_verifier = rtrim(strtr(base64_encode($verifier_bytes), "+/", "-_"), "=");


        setcookie("verifier_c", "$code_verifier", time() + 3600, "/");


        //code challenge
        $challenge_bytes = hash("sha256", $code_verifier, true);
        $code_challenge  = rtrim(strtr(base64_encode($challenge_bytes), "+/", "-_"), "=");

        $input = $serviceId . $aeskey . $state . $nonce . $redirect_uri . $scope . $code_challenge;

        //apiHmac
        $apiHmac = hash_hmac('sha256', $input, $aeskey, true);
        $apiHmac = base64_encode($apiHmac);
        $finalUrl = $url . "?&scope=" . $scope . "&response_type=" . $response_type . "&redirect_uri=" . $redirect_uri . "&state=" . $state . "&code_challenge_method=" . $code_challenge_method . "&nonce=" . $nonce . "&client_id=" . $serviceId . "&code_challenge=" . $code_challenge . "&request_uri=" . $request_uri . "&apiHmac=" . $apiHmac;

        $data['url']           = $finalUrl;
        $data['nonce']         = $nonce;
        $data['code_verifier'] = $code_verifier;
        return responseMsgs(true, "Success", $data, "", "01", responseTime(), "POST", "");
    }

    /**
     * | Dashboard
     */
    public function dashboardEpramaan(Request $req)
    {
        $type          = $req->type;
        $code          = $req->code;
        $nonce         = $req->nonce;
        $code_verifier = $req->codeVerifier;
        $scope         = 'openid';
        $grant_type    = 'authorization_code';
        $epramaanTokenRequestUrl = 'https://epstg.meripehchaan.gov.in/openid/jwt/processJwtTokenRequest.do';
        // $serviceId      = '100001033';
        // $redirect_uri = 'http://site2.aadrikainfomedia.in/citizen'; //sso success Url as given while registration

        switch ($type) {
            case 'citizen':
                $serviceId    = '100001033';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/citizen/login/e-pramaan';
                break;

            case 'property':
                $serviceId    = '100001034';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/property/login/e-pramaan';
                break;

            case 'water':
                $serviceId    = '100001035';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/water/login/e-pramaan';
                break;

            case 'trade':
                $serviceId    = '100001036';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/trade/login/e-pramaan';
                break;

            case 'advertisement':
                $serviceId    = '100001037';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/advertisement/login/e-pramaan';
                break;

            case 'pet':
                $serviceId    = '100001038';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/pet/login/e-pramaan';
                break;

            case 'marriage':
                $serviceId    = '100001039';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/marriage/login/e-pramaan';
                break;

            case 'agency':
                $serviceId    = '100001041';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/agency/login/e-pramaan';
                break;

            default:
                $serviceId    = '100001033';
                $redirect_uri = 'http://site2.aadrikainfomedia.in/citizen/login/e-pramaan';
                break;
        }

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL            => $epramaanTokenRequestUrl,
                CURLOPT_RETURNTRANSFER => true,
                //CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_POSTFIELDS     => '{
					"code"          : ["' . $code . '"],
					"grant_type"    : ["' . $grant_type . '"],
					"scope"         : ["' . $scope . '"],
					"redirect_uri"  : ["' . $redirect_uri . '"],
					"request_uri"   : ["' . $epramaanTokenRequestUrl . '"],
					"code_verifier" : ["' . $code_verifier . '"],
					"client_id"     : ["' . $serviceId . '"]}',
                CURLOPT_HTTPHEADER     => array(
                    'Content-Type: application/json'
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);

        //---------processing token-decrypt--------------
        // The key encryption algorithm manager with the A256KW algorithm.
        $keyEncryptionAlgorithmManager = new AlgorithmManager([
            new A256KW(),
        ]);
        // The content encryption algorithm manager with the A256CBC-HS256 algorithm.
        $contentEncryptionAlgorithmManager = new AlgorithmManager([
            new A256GCM(),
        ]);
        $compressionMethodManager = new CompressionMethodManager([
            new Deflate(),

        ]);

        // AES key Generation.
        $sha25 = hash('SHA256', $nonce, true);
        $jwk = new JWK([
            'kty' => 'oct',
            'k' => $this->base64url_encode($sha25),
        ]);

        //decryption
        $jweDecrypter = new JWEDecrypter(
            $keyEncryptionAlgorithmManager,
            $contentEncryptionAlgorithmManager,
            $compressionMethodManager
        );
        // The serializer manager(JWE Compact Serialization Mode)
        $serializerManager = new JWESerializerManager([
            new CompactSerializer(),
        ]);
        // return $response;
        // print_r($response);
        // exit();
        // load the token.
        $jwe = $serializerManager->unserialize($response);
        //decrypt the token
        $success = $jweDecrypter->decryptUsingKey($jwe, $jwk, 0);

        if ($success) {
            $jweLoader = new JWELoader($serializerManager, $jweDecrypter, null);
            $jwe = $jweLoader->loadAndDecryptWithKey($response, $jwk, $recipient);
            $decryptedtoken = $jwe->getPayload();
            setcookie("decryptedtoken_c", "$decryptedtoken", time() + 3600, "/");
        } else {
            throw new RuntimeException('Error Decrypting JWE');
        }
        //Verifying token with the certificate shared by epramaan
        // The algorithm manager with the HS256 algorithm.
        $algorithmManager = new AlgorithmManager([
            new RS256(),
        ]);
        // JWS Verifier.
        $jwsVerifier = new JWSVerifier($algorithmManager);
        $key = JWKFactory::createFromCertificateFile(
            // getcwd() . '/epramaan.crt',
            // '/var/www/html/Authorisation-Service/storage/app/public/epramaan.crt', // The path where the certificate has been stored
            'D:\epramaan.crt', // The path where the certificate has been stored
            [
                'use' => 'sig', // Additional parameters
            ]
        );

        $serializerManager = new JWSSerializerManager([
            new SignatureCompactSerializer(),
        ]);

        $jws = $serializerManager->unserialize($decryptedtoken);
        $isVerified = $jwsVerifier->verifyWithKey($jws, $key, 0);

        $jwsLoader = new JWSLoader(
            $serializerManager,
            $jwsVerifier,
            null
        );

        $jws = $jwsLoader->loadAndVerifyWithKey($decryptedtoken, $key, $signature);
        $payload = $jws->getPayload();
        $payload = json_decode($payload);
        $mEpramaanLogin = new EpramaanLogin();
        $epReqs = [
            "unique_user_id"    => $payload->sub,
            "single_signon_id"  => $payload->sso_id,
            "token_identifier"  => $payload->jti,
            "token_issue_time"  => Carbon::createFromTimestamp($payload->iat),
            "token_expiry_time" => Carbon::createFromTimestamp($payload->exp),
            "service_id"        => $payload->aud,
            "session_id"        => $payload->session_id,
            "name"              => $payload->name ?? "",
            "email"             => $payload->email ?? "",
            "mobile"            => $payload->mobile_number ?? "",
            "dob"               => $payload->dob ?? "",
            "gender"            => $payload->gender ?? "",
            "house"             => $payload->house ?? "",
            "locality"          => $payload->locality ?? "",
            "pincode"           => $payload->pincode ?? "",
            "district"          => $payload->district ?? "",
            "state"             => $payload->state ?? "",
            "aadhar_ref_no"     => $payload->aadhar_ref_no ?? "",
            "user_name"         => $payload->user_name ?? "",
            "respose_json"      => json_encode($payload),
        ];
        $epramaanDtl = $mEpramaanLogin->store($epReqs);

        if ($type = 'citizen') {
            $mActiveCitizen = new ActiveCitizen();
            $citizenInfo = $mActiveCitizen->getCitizenByUniqueId($payload->sub);

            if (!$citizenInfo) {
                #_save citizen data
                $saveReqs = [
                    "user_name"         => $payload->name ?? "",
                    "mobile"            => $payload->mobile_number ?? "",
                    "email"             => $payload->email ?? "",
                    "gender"            => $payload->gender ?? "",
                    "dob"               => $payload->dob ?? "",
                    "unique_user_id"    => $payload->sub,
                    "token_identifier"  => $payload->jti,
                    "aadhar_ref_no"     => $payload->aadhar_ref_no ?? "",
                ];
                $citizenInfo =  $mActiveCitizen->citizenRegistration($saveReqs);
            }

            #_update token
            $token = $citizenInfo->createToken('my-app-token')->plainTextToken;
            $citizenInfo->remember_token = $token;
            $citizenInfo->save();

            #_login details
            $userDetails['id']        = $citizenInfo->id;
            $userDetails['userName']  = $citizenInfo->user_name;
            $userDetails['mobile']    = $citizenInfo->mobile;
            $userDetails['userType']  = $citizenInfo->user_type;
            $userDetails['user_type'] = $citizenInfo->user_type;
            $userDetails['token']     = $token;
            return responseMsgs(true, "Login Successfully", $userDetails, "", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    /**
     * |
     */
    public function base64url_encode($data)
    {
        // encode $data to Base64 string
        $b64 = base64_encode($data);
        // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
        $url = strtr($b64, '+/', '-_');
        // Remove padding character from the end of line and return the Base64URL result
        return rtrim($url, '=');
    }
}
