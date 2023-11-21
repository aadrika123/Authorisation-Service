<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 ** Use following packages for E-parmaan
 **/

use Jose\Component\Encryption\JWEDecrypterFactory;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256KW;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\JWELoader;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
//use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\JWSLoader;

class EpramaanController extends Controller
{

    /**
     * 
     */
    public function loginEpramaan()
    {
        setcookie("verifier_c", "", time() - 3600, "/");
        setcookie("nonce_c", "", time() - 3600, "/");
        $scope = 'openid';
        $redirect_uri = 'http://site2.aadrikainfomedia.in/citizen/authResponseConsumer.do';
        $response_type = 'code';
        $code_challenge_method = 'S256';
        $serviceId = '100001031';
        $aeskey = 'fddbb838-b6b1-44c4-93b3-dc9ee91f174a';
        $request_uri = 'https://epstg.meripehchaan.gov.in/openid/jwt/processJwtAuthGrantRequest.do';
        $url = 'https://epstg.meripehchaan.gov.in/openid/jwt/processJwtAuthGrantRequest.do';


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

        $code_challenge = rtrim(strtr(base64_encode($challenge_bytes), "+/", "-_"), "=");


        $input = $serviceId . $aeskey . $state . $nonce . $redirect_uri . $scope . $code_challenge;

        //apiHmac
        $apiHmac = hash_hmac('sha256', $input, $aeskey, true);
        $apiHmac = base64_encode($apiHmac);
        $finalUrl = $url . "?&scope=" . $scope . "&response_type=" . $response_type . "&redirect_uri=" . $redirect_uri . "&state=" . $state . "&code_challenge_method=" . $code_challenge_method . "&nonce=" . $nonce . "&client_id=" . $serviceId . "&code_challenge=" . $code_challenge . "&request_uri=" . $request_uri . "&apiHmac=" . $apiHmac;

        return responseMsgs(true, "Success", $finalUrl, "", "01", responseTime(), "POST", "");
    }
}
