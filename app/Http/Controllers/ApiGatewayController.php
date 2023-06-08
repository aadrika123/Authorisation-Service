<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class ApiGatewayController extends Controller
{
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
            $method = $req->method();
            $req = $req->merge([
                'auth' => authUser(),
                'token' => $req->bearerToken(),
                'currentAccessToken' => $req->user()->currentAccessToken(),
                'apiToken' => $req->user()->currentAccessToken()->token
            ]);
            $response = Http::withHeaders([
                'API-KEY' => collect($req->headers)->toArray()['api-key']
            ])
                ->$method(
                    $url . $req->getRequestUri(),
                    $req->all()
                );

            return $response;
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
}
