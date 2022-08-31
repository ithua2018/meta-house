<?php

namespace App\Http\Middleware;

use App\Constants\UserConstants;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckIfLandlord
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::guard('api')->user();
        if($user['current_role'] != UserConstants::USER_ROLE_LANDLOARD) {
            return response()->json([
                'success' => false,
                'data' =>[],
                'errorCode' => 402,
                'errorMessage' => '你不是房东,没有权限操作'
            ],401);
        }
        return $next($request);
    }
}
