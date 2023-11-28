<?php

namespace App\Http\Controllers;

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
    public function loginEpramaan()
    {
        setcookie("verifier_c", "", time() - 3600, "/");
        setcookie("nonce_c", "", time() - 3600, "/");
        $scope = 'openid';
        // $redirect_uri = 'http://site2.aadrikainfomedia.in/citizen/authResponseConsumer.do'; //it is working
        $redirect_uri = 'http://site2.aadrikainfomedia.in/citizen/login/e-pramaan';
        $serviceId = '100001033';
        $response_type = 'code';
        $code_challenge_method = 'S256';
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
        $code          = $req->code;
        $nonce         = $req->nonce;
        $code_verifier = $req->codeVerifier;
        $epramaanTokenRequestUrl = 'https://epstg.meripehchaan.gov.in/openid/jwt/processJwtTokenRequest.do';
        $redirectionURI = 'http://site2.aadrikainfomedia.in/citizen'; //sso success Url as given while registration
        $serviceId = '100001033';
        $grant_type = 'authorization_code';
        $scope = 'openid';

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
					"redirect_uri"  : ["' . $redirectionURI . '"],
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
            getcwd() . '/epramaan.crt',
            // '/var/www/html/Authorisation-Service/storage/app/public/epramaan.crt', // The path where the certificate has been stored
            // 'D:\epramaan.crt', // The path where the certificate has been stored
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
        return  $payload = $jws->getPayload();
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
