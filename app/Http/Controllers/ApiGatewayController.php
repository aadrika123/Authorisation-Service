<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

use App\BLL\AuthorizationBll;

class ApiGatewayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['unAuthApis']);
    }

    /**
     * | Check Points to check API
     */
    public function checkPoints(Request $req)
    {
        $segments = explode('/', $req->path());
        $service = $segments[2];
        if ($service == 'auth')
            $this->unAuthApis($req);
        else
            $this->apiGatewayService($req);
    }


    public function apiGatewayService(Request $req)
    {

        try {
            // Converting environmental variables to Services
            $baseURLs = Config::get('constants.MICROSERVICES_APIS');
            $services = json_decode($baseURLs, true);
            // Sending to Microservices
            $segments = explode('/', $req->path());
            $service = $segments[1];
            if (!array_key_exists($service, $services))
                throw new Exception("Service Not Available");

            $url = $services[$service];
            $bearerToken = (collect(($req->headers->all())['authorization'] ?? "")->first());
            $ipAddress = getClientIpAddress();
            $method = $req->method();
            if ($segments[1] == "trade" && strtolower($req->getMethod()) == "get") {
            } else {
                $req = $req->merge([
                    'auth' => authUser(),
                    'token' => $req->bearerToken(),
                    'currentAccessToken' => $req->user()->currentAccessToken(),
                    'apiToken' => $req->user()->currentAccessToken()->token,
                    'ipAddress' => $ipAddress
                ]);
            }

            #======================

            $response = Http::withHeaders(
                [
                    "Authorization" => "Bearer $bearerToken",
                    'API-KEY' => collect($req->headers)->toArray()['api-key'] ?? "",
                ]
            );
            $files = [];
            if (!empty($_FILES)) {
                $mAuthorizationBll = new AuthorizationBll();
                $files = $mAuthorizationBll->addFiles($_FILES, $response);
            }
            $textfields = $mAuthorizationBll->addTextFields($req, $response);
            $fileName = [];
            // $new = [];
            foreach ($_FILES as $index => $val) {
                array_push($fileName, $index);
            }

            // foreach (collect($req->all())->toArray() as $key => $val) {
            //     $new[$key] = $val;
            // }
            // $dotIndexes = $this->generateDotIndexes($_FILES);

            // foreach ($dotIndexes as $val) {
            //     $patern = "/\.name/i";
            //     if (!preg_match($patern, $val)) {
            //         continue;
            //     }
            //     $name = "";
            //     $test = collect(explode(".", preg_replace($patern, "", $val)));
            //     $t = $test->filter(function ($val, $index) {
            //         return $index > 0 ? true : "";
            //     });
            //     $t = $t->map(function ($val) {
            //         return "[" . $val . "]";
            //     });
            //     $name = (($test[0]) . implode("", $t->toArray()));
            //     $response = $response->attach(
            //         $name,
            //         file_get_contents($this->getArrayValueByDotNotation($_FILES, preg_replace($patern, ".tmp_name", $val))),
            //         $this->getArrayValueByDotNotation($_FILES, $val)
            //     );
            // }
            // $textIndex = $this->generateDotIndexes($new);
            // $new2 = [];
            // foreach ($textIndex as $val) {
            //     $name = "";
            //     $test = collect(explode(".", $val));
            //     $t = $test->filter(function ($val, $index) {
            //         return $index > 0 ? true : "";
            //     });
            //     $t = $t->map(function ($val) {
            //         return "[" . $val . "]";
            //     });
            //     $name = (($test[0]) . implode("", $t->toArray()));
            //     $new2[] = [
            //         "contents" => $this->getArrayValueByDotNotation($new, $val),
            //         "name" => $name
            //     ];
            // }

            # Check if the response is valid to return in json format 
            // $response = $response->$method($url . $req->getRequestUri(), ($fileName ? $new2 : $new));
           $response = $response->$method($url . $req->getRequestUri(), ($fileName ? $textfields : $files));
            if (isset(json_decode($response)->status)) {
                if (json_decode($response)->status == false) {
                    return json_decode($response);
                }
                return json_decode($response);
            } else {
                return $response;
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    public function getArrayValueByDotNotation(array $array, string $key)
    {
        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                return null; // Key doesn't exist in the array
            }
        }

        return $array;
    }

    public function generateDotIndexes(array $array, $prefix = '', $result = [])
    {

        foreach ($array as $key => $value) {
            $newKey = $prefix . $key;
            if (is_array($value)) {
                $result = $this->generateDotIndexes($value, $newKey . '.', $result);
            } else {
                $result[] = $newKey;
            }
        }
        return $result;
    }
}
