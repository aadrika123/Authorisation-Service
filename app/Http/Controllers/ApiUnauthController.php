<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

use App\BLL\AuthorizationBll;
use Illuminate\Http\Client\PendingRequest;

class ApiUnauthController extends Controller
{
    private  $ApiGatewayController;
    public function __construct()
    {
        $this->ApiGatewayController = new ApiGatewayController();
    }

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
            $method = $req->method();

            $req = $req->merge([
                'token' => $req->bearerToken(),
                'ipAddress' => $ipAddress
            ]);
            #======================
            $header = [];
            foreach ($this->ApiGatewayController->generateDotIndexes(($req->headers->all())) as $key) {
                $val = explode(".", $key)[0] ?? "";
                if (in_array($val, ["host", "accept", "content-length", ($_FILES) ? "content-type" : ""])) {
                    continue;
                }
                if (strtolower($val) == "content-type" && preg_match("/multipart/i", $this->ApiGatewayController->getArrayValueByDotNotation(($req->headers->all()), $key)) && !($_FILES)) {

                    continue;
                }
                $header[explode(".", $key)[0] ?? ""] = $this->ApiGatewayController->getArrayValueByDotNotation(($req->headers->all()), $key);
            }
            $response = Http::withHeaders(
                $header
            );
            $new2 = [];
            if ($_FILES) {
                $response = $this->ApiGatewayController->fileHandeling($response);
                $new2 = $this->ApiGatewayController->inputHandeling($req);
            }

            # Check if the response is valid to return in json format 
            $response = $response->$method($url . $req->getRequestUri(), ($_FILES ? $new2 : $req->all()));

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

}
