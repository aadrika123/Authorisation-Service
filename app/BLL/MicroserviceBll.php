<?php

namespace App\Bll;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class MicroserviceBll
{
    public function checkAllServices()
    {
        $services = Config::get('constants.MICROSERVICES_APIS');

        // Decode JSON if it's a string
        if (is_string($services)) {
            $services = json_decode($services, true);
        }

        if (!is_array($services)) {
            $services = [];
        }

        $results = ['services' => []];

        foreach ($services as $name => $url) {
            $results['services'][$name] = $this->checkService($url);
        }

        return response()->json($results, 200);
    }

    private function checkService($url)
    {
        try {
            $start = microtime(true);

            $response =  Http::timeout(2)->withoutVerifying()->get($url);

            $timeTaken = round((microtime(true) - $start) * 1000, 2); // ms

            return [
                'url'           => $url,
                'status'        => $response->successful() ? 'UP' : 'DOWN',
                'response_code' => $response->status(),
                'response_time' => $timeTaken . ' ms',
            ];
        } catch (\Exception $e) {
            return [
                'url'           => $url,
                'status'        => 'DOWN',
                'response_code' => 0,
                'error'         => str_contains($e->getMessage(), 'Timeout')
                    ? 'Service timeout'
                    : $e->getMessage(),
            ];
        }
    }
}
