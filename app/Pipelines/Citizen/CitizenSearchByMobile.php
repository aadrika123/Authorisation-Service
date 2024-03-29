<?php

namespace App\Pipelines\Citizen;

use Closure;

class CitizenSearchByMobile
{
    public function handle($request, Closure $next)
    {
        if (!request()->has('mobile')) {
            return $next($request);
        }
        if (request()->has("strict") == true) {
            return $next($request)->where("mobile", request()->input('mobile'));
        }
        return $next($request)
            ->where('mobile', 'ilike', '%' . request()->input('mobile') . '%');
    }
}
