<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

use App\BLL\AuthorizationBll;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Client\PendingRequest;

class ApiUnauthController extends Controller
{
    public function anuthinticatedApiGateway(Request $req)
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
            // $bearerToken = (collect(($req->headers->all())['authorization'] ?? "")->first());
            $ipAddress = getClientIpAddress();
            $req = $req->merge([
                'token' => $req->bearerToken(),
                'ipAddress' => $ipAddress
            ]);
            $method = $req->method();
            $client = new Client();
            $promises = [];
            $asyncMethod = in_array($method, ['POST', 'post']) ? 'postAsync' : 'getAsync';

            $promise = $client->$asyncMethod(
                $url . $req->getRequestUri(),
                ['json' => $req->all()]
            ); // Create an async HTTP POST request
            // Wait for the promise to complete
            $promises[] = $promise;
            $responses = Promise\Utils::settle($promises)->wait();
            // Process the response
            $response = $responses[0];

            if ($response['state'] === Promise\PromiseInterface::FULFILLED) {
                return $responseBody = $response['value']->getBody()->getContents();
                // Process the response body as needed
            } else {
                // Handle failed requests
                return $errorMessage = $response['reason']->getMessage();
                // Handle the error message as needed
            }

            // // Converting environmental variables to Services
            // $baseURLs = Config::get('constants.MICROSERVICES_APIS');
            // $services = json_decode($baseURLs, true);
            // // Sending to Microservices
            // $segments = explode('/', $req->path());
            // $service = $segments[1];
            // if (!array_key_exists($service, $services))
            //     throw new Exception("Service Not Available");

            // $url = $services[$service];
            // // $bearerToken = (collect(($req->headers->all())['authorization'] ?? "")->first());
            // $ipAddress = getClientIpAddress();
            // $method = $req->method();

            // $req = $req->merge([
            //     'token' => $req->bearerToken(),
            //     'ipAddress' => $ipAddress
            // ]);
            // #======================
            // $header = [];
            // foreach ($this->generateDotIndexes(($req->headers->all())) as $key) {
            //     $val = explode(".", $key)[0] ?? "";
            //     if (in_array($val, ["host", "accept", "content-length", ($_FILES) ? "content-type" : ""])) {
            //         continue;
            //     }
            //     if (strtolower($val) == "content-type" && preg_match("/multipart/i", $this->getArrayValueByDotNotation(($req->headers->all()), $key)) && !($_FILES)) {

            //         continue;
            //     }
            //     $header[explode(".", $key)[0] ?? ""] = $this->getArrayValueByDotNotation(($req->headers->all()), $key);
            // }
            // $response = Http::withHeaders(
            //     $header
            // );
            // $new2 = [];
            // if ($_FILES) {
            //     $response = $this->fileHandeling($response);
            //     $new2 = $this->inputHandeling($req);
            // }

            // # Check if the response is valid to return in json format 
            // $response = $response->$method($url . $req->getRequestUri(), ($_FILES ? $new2 : $req->all()));

            // if (isset(json_decode($response)->status)) {
            //     if (json_decode($response)->status == false) {
            //         return json_decode($response);
            //     }
            //     return json_decode($response);
            // } else {
            //     return $response;
            // }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    public function fileHandeling(PendingRequest $req)
    {
        $fileName = [];
        foreach ($_FILES as $index => $val) {
            array_push($fileName, $index);
        }

        $dotIndexes = $this->generateDotIndexes($_FILES);

        foreach ($dotIndexes as $val) {
            $patern = "/\.name/i";
            if (!preg_match($patern, $val)) {
                continue;
            }
            $name = "";
            $test = collect(explode(".", preg_replace($patern, "", $val)));
            $t = $test->filter(function ($val, $index) {
                return $index > 0 ? true : "";
            });
            $t = $t->map(function ($val) {
                return "[" . $val . "]";
            });
            $name = (($test[0]) . implode("", $t->toArray()));
            $req = $req->attach(
                $name,
                file_get_contents($this->getArrayValueByDotNotation($_FILES, preg_replace($patern, ".tmp_name", $val))),
                $this->getArrayValueByDotNotation($_FILES, $val)
            );
        }
        return $req;
    }
    public function inputHandeling(Request $req)
    {
        $inputs = [];
        $new = [];
        foreach (collect($req->all())->toArray() as $key => $val) {
            $new[$key] = $val;
        }
        $textIndex = $this->generateDotIndexes($new);
        foreach ($textIndex as $val) {
            $name = "";
            $test = collect(explode(".", $val));
            $t = $test->filter(function ($val, $index) {
                return $index > 0 ? true : "";
            });
            $t = $t->map(function ($val) {
                return "[" . $val . "]";
            });
            $name = (($test[0]) . implode("", $t->toArray()));
            $inputs[] = [
                "contents" => $this->getArrayValueByDotNotation($new, $val),
                "name" => $name
            ];
        }

        return $inputs;
    }

    public function getArrayValueByDotNotation($array, string $key)
    {
        if (!is_array($array)) {
            $array = $array->toArray();
        }
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

    public function generateDotIndexes($array, $prefix = '', $result = [])
    {
        if (!is_array($array)) {
            $array = $array->toArray();
        }
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
