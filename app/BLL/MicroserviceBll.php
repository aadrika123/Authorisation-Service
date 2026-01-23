<?php

namespace App\BLL;

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

        foreach ($services as $name => $baseUrl) {
            $url = rtrim($baseUrl, '/') . '/api/' . $name . '/health-check';
            $displayUrl = rtrim(config('app.url'), '/') . '/api/' . $name . '/health-check';
            $results['services'][$name] = $this->checkService($url, $displayUrl);
        }

        return response()->json($results, 200);
    }

    private function checkService($url, $displayUrl = null)
    {
        $displayUrl = $displayUrl ?? $url;
        try {
            $start = microtime(true);

            $response =  Http::timeout(2)->withoutVerifying()->get($url);

            $timeTaken = round((microtime(true) - $start) * 1000, 2); // ms

            return [
                'url'           => $displayUrl,
                'status'        => $response->status() === 200 ? 'UP' : 'DOWN',
                'response_code' => $response->status(),
                'response_time' => $timeTaken . ' ms',
            ];
        } catch (\Exception $e) {
            return [
                'url'           => $displayUrl,
                'status'        => 'DOWN',
                'response_code' => 0,
                'error'         => str_contains($e->getMessage(), 'Timeout')
                    ? 'Service timeout'
                    : $e->getMessage(),
            ];
        }
    }
}
