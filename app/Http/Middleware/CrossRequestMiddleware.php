<?php


namespace App\Http\Middleware;

use Closure;

class CrossRequestMiddleware
{

    public function handle($request, Closure $next)
    {

        $response = $next($request);
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');

        return $response;
    }

}
