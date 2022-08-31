<?php

namespace App\Http\Middleware;

use App\Constants\ResponseCode;
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
        if ($request->path() == 'm/user/regPwd'){
            //首次登录设置密码时，先不校验用户身份,客户端可以通过post m/me 返回的userinfo=>role来判断.
            return $next($request);
        }
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
