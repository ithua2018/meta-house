<?php

namespace App\Http\Middleware;

use App\Constants\ResponseCode;
use App\Constants\UserConstants;
use App\Models\UsersInformation;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckHasIdentity
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
           // console_debug($user);
            if(empty($user['current_role'])) {
                return response()->json([
                    'success' => false,
                    'data' =>[],
                    'errorCode' => ResponseCode::AUTH_NOT_SELECT_ROLE[0],
                    'errorMessage' => ResponseCode::AUTH_NOT_SELECT_ROLE[1]
                ],401);
            } else {
                $userInfo = UsersInformation::query()->where(['user_id' => $user['id'], 'role'=>$user['current_role']])->get()->toArray();
               if(empty($userInfo)) {
                   return response()->json([
                       'success' => false,
                       'data' =>[],
                       'errorCode' => ResponseCode::AUTH_NOT_SELECT_ROLE[0],
                       'errorMessage' => ResponseCode::AUTH_NOT_SELECT_ROLE[1]
                   ],401);
               }
            }
        return $next($request);
    }
}
