<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiGatewayController extends Controller
{
    public function apiGatewayService(Request $req)
    {
        try {
            $baseURLs = [
                'property' => 'http://127.0.0.1:8001',
                'water' => 'http://127.0.0.1:8001',
                'trade' => 'http://127.0.0.1:8001',
                'advert' => 'http://127.0.0.1:8001',
                'water-tanker' => 'http://192.168.0.21:8001',
                'menu' => 'http://192.168.0.104:8000'
                // Add more microservices here
            ];
            $segments = explode('/', $req->path());
            $service = $segments[1];
            if (!array_key_exists($service, $baseURLs))
                throw new Exception("Service Not Available");

            $url = $baseURLs[$service];
            $method = $req->method();
            $req = $req->merge(['auth' => authUser()]);
            $response = Http::$method($url . $req->getRequestUri(), $req->all());
            return $response;
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
}
