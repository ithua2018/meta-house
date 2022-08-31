<?php

namespace App\Services;

use App\Http\Requests\Mobile\Request;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UserService  extends BaseService
{
    /**
     * 注册用户
     * @param Request $request
     * @return mixed
     */
   public function store(Request $request)
   {
       $info = [
           'username' => $request->mobile,
           'mobile' => $request->mobile,
           'register_time' => time(),
           'login_time' => time(),
           'login_ip' => request()->ip()
       ];
       return User::create($info);
   }

    /**
     * 验证手机号发送验证码是否达到限制条数
     * @param  string  $mobile
     * @return bool
     */
    public function checkMobileSendCaptchaCount(string $mobile)
    {
        $countKey = 'register_captcha_count|'.$mobile;
        if (Cache::has($countKey)) {
            $count = Cache::increment($countKey);
            if ($count > 10) {
                return false;
            }
        } else {
            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        }
        return true;
    }


}
