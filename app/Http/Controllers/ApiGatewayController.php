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
            $bearerToken = (collect(($req->headers->all())['authorization']??"")->first());
            $contentType = (collect(($req->headers->all())['content-type'] ?? "")->first());
            $method = $req->method();
            $req = $req->merge([
                'auth' => authUser(),
                'token' => $req->bearerToken(),
                'currentAccessToken' => $req->user()->currentAccessToken(),
                'apiToken' => $req->user()->currentAccessToken()->token,
            ]);           


            #======================
            if($service!="public-transport")
            {
                dd($_FILES);
            } 
            $response = Http::withHeaders(
                [
                    "Authorization" => "Bearer $bearerToken",
                    'API-KEY' => collect($req->headers)->toArray()['api-key'] ?? "",    
                ]
            );
            $fileName = [];
            $new =[];  
            // if($service!="public-transport")
            {          
            foreach($_FILES as $index=>$val)
            {
                array_push($fileName,$index);
            }
            
                // dd($_FILES);
            } 
            // dd($fileName);
            foreach($req->all() as $key=>$val)
            {
                if(in_array($key,$fileName))
                {
                    continue;
                }
                $new[$key] =(is_array($val))?json_encode($val):$val;            
            }
            
            foreach($fileName as $val)
            {
                $response = $response->attach(
                    $val,
                    file_get_contents($_FILES[$val]["tmp_name"]),
                    $_FILES[$val]["name"]
                );
            }
            
            $response = $response->$method($url . $req->getRequestUri(),$new);
            #======================
            
            return $response;
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }




    //     public function apiGatewayService(Request $req)
    //     {

    //        return $files = $_FILES['documents'];
    //         // $data = $req->input('data');

    //         $httpClient = Http::attach(
    //             'file1',
    //             file_get_contents($files['documents'][0]['temp_name']->getPathname()),
    //             $files[0]->getClientOriginalName()
    //         );

    //         for ($i = 1; $i < count($files); $i++) {
    //             $httpClient = $httpClient->attach(
    //                 'file' . ($i + 1),
    //                 file_get_contents($files[$i]->getPathname()),
    //                 $files[$i]->getClientOriginalName()
    //             );
    //         }
    // return  $httpClient;
    //         $response = $httpClient->post('http://destination-url', [
    //             'additional_data' => $data,
    //         ]);
    //     }
}
