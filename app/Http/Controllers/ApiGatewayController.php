<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class ApiGatewayController extends Controller
{
    // public function apiGatewayService(Request $req)
    // {
    //     try {
    //         // Converting environmental variables to Services
    //         $baseURLs = Config::get('constants.MICROSERVICES_APIS');
    //         $services = json_decode($baseURLs, true);
    //         // Sending to Microservices
    //         $segments = explode('/', $req->path());
    //         $service = $segments[1];
    //         if (!array_key_exists($service, $services))
    //             throw new Exception("Service Not Available");

    //         $url = $services[$service];
    //         $bearerToken = (collect(($req->headers->all())['authorization']??"")->first());
    //         $contentType = (collect(($req->headers->all())['content-type'] ?? "")->first());
    //         $method = $req->method();
    //         $req = $req->merge([
    //             'auth' => authUser(),
    //             'token' => $req->bearerToken(),
    //             'currentAccessToken' => $req->user()->currentAccessToken(),
    //             'apiToken' => $req->user()->currentAccessToken()->token,
    //         ]);           



    //         #======================

    //         $response = Http::withHeaders(
    //             [
    //                 "Authorization" => "Bearer $bearerToken",
    //                 'API-KEY' => collect($req->headers)->toArray()['api-key'] ?? "",    
    //             ]
    //         );
    //         $fileName = [];
    //         $new =[]; 
    //         foreach($_FILES as $index=>$val)
    //         {
    //             array_push($fileName,$index);
    //         }

    //         foreach(collect($req->all())->toArray() as $key=>$val)
    //         {
    //             if(in_array($key,$fileName))
    //             {
    //                 continue;
    //             } 
    //             $new[$key] = $val;          
    //         }
    //         $dotIndexes = $this->generateDotIndexes($_FILES); 

    //         foreach($dotIndexes as $val)
    //         {
    //             $patern = "/\.name/i";
    //             if(!preg_match($patern,$val))
    //             {
    //                 continue;
    //             }
    //             $name = "";
    //             $test = collect(explode(".",preg_replace($patern,"",$val)));
    //             $t = $test->filter(function($val,$index){
    //                 return $index > 0 ? true : "" ;
    //             });
    //             $t = $t->map(function($val){
    //                 return "[".$val."]";
    //             });
    //             $name = (($test[0]).implode("",$t->toArray()));
    //             $response = $response->attach(
    //                 $name,
    //                 file_get_contents($this->getArrayValueByDotNotation($_FILES,preg_replace($patern,".tmp_name",$val))),
    //                 $this->getArrayValueByDotNotation($_FILES,$val)                    
    //             );
    //         }
    //         $textIndex = $this->generateDotIndexes($new);            
    //         $new2 = [];           
    //         foreach($textIndex as $val)            
    //         {
    //             $name = "";
    //             $test = collect(explode(".",$val));
    //             $t = $test->filter(function($val,$index){
    //                 return $index > 0 ? true : "" ;
    //             });
    //             $t = $t->map(function($val){
    //                 return "[".$val."]";
    //             });
    //             $name = (($test[0]).implode("",$t->toArray()));
    //             $new2[]= [                    
    //                 "contents"=>$this->getArrayValueByDotNotation($new,$val),
    //                 "name"=>$name                    
    //             ];
    //         }
    //         $response = $response->$method($url . $req->getRequestUri(),($fileName?$new2:$new));
    //         #======================

    //         return $response;
    //     } 
    //     catch (Exception $e) 
    //     {
    //         return responseMsgs(false, $e->getMessage(), "");
    //     }
    // }

    // public function getArrayValueByDotNotation(array $array, string $key)
    // {
    //     $keys = explode('.', $key);

    //     foreach ($keys as $key) 
    //     {
    //         if (isset($array[$key])) 
    //         {
    //             $array = $array[$key];
    //         } 
    //         else 
    //         {
    //             return null; // Key doesn't exist in the array
    //         }
    //     }

    //     return $array;
    // }

    // public function generateDotIndexes(array $array, $prefix = '', $result = [])
    // {    

    //     foreach ($array as $key => $value) 
    //     {            
    //         $newKey = $prefix . $key; 
    //         if (is_array($value)) 
    //         {                
    //             $result = $this->generateDotIndexes($value, $newKey . '.', $result);
    //         } 
    //         else 
    //         {                
    //             $result[] = $newKey;
    //         }
    //     }
    //     return $result;
    // }

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
            $contentType = (collect(($req->headers->all())['content-type'] ?? "")->first());
            $ipAddress = getClientIpAddress();
            $method = $req->method();
            $req = $req->merge([
                'auth' => authUser(),
                'token' => $req->bearerToken(),
                'currentAccessToken' => $req->user()->currentAccessToken(),
                'apiToken' => $req->user()->currentAccessToken()->token,
                'ipAddress' => $ipAddress
            ]);



            #======================

            $response = Http::withHeaders(
                [
                    "Authorization" => "Bearer $bearerToken",
                    'API-KEY' => collect($req->headers)->toArray()['api-key'] ?? "",
                ]
            );
            $fileName = [];
            $new = [];
            foreach ($_FILES as $index => $val) {
                array_push($fileName, $index);
            }

            foreach (collect($req->all())->toArray() as $key => $val) {
                $new[$key] = $val;
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
                $response = $response->attach(
                    $name,
                    file_get_contents($this->getArrayValueByDotNotation($_FILES, preg_replace($patern, ".tmp_name", $val))),
                    $this->getArrayValueByDotNotation($_FILES, $val)
                );
            }
            $textIndex = $this->generateDotIndexes($new);
            $new2 = [];
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
                $new2[] = [
                    "contents" => $this->getArrayValueByDotNotation($new, $val),
                    "name" => $name
                ];
            }

            $response = $response->$method($url . $req->getRequestUri(), ($fileName ? $new2 : $new));

            if(isset($response['status'])){
                if ($response['status'] == false)
                    throw new Exception($response['message']);
            }
            #======================
            return json_decode($response);
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
