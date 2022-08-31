<?php
namespace  App\Http\Controllers\Mobile;
use App\Constants\ResponseCode;
use App\Http\Requests\Mobile\UserLoginRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\SmsService;
use App\Services\UserService;
use Illuminate\Hashing\HashManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Collection;


class AuthController extends  AbstractApiController
{
    private UserRepository $userRepository;
    private HashManager $hash;
    private SmsService $smsService;
    private UserService $userService;
    public function __construct(
        UserRepository $userRepository,
        HashManager $hash,
        SmsService $smsService,
        UserService $userService

    )
    {
       $this->userRepository = $userRepository;
       $this->hash = $hash;
       $this->smsService = $smsService;
       $this->userService = $userService;
    }

    /**
     * 注册登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function regLog(UserLoginRequest $request)
   {
       $data = $request->only(['mobile', 'password', 'login_type', 'code']);

        /**@var User | null $user */
        $user = $this->userRepository->getFirstWhere('mobile', $data['mobile']);
        //密码登录的情况，找不到用户
       if($data['login_type'] ==2) { //密码登录
           if( !$user) {
               return $this->fail(ResponseCode::AUTH_NOT_FOUND_ACCOUNT );
           } else {
               if(!$this->hash->check($data['password'], $user->password)) {
                   return $this->fail(ResponseCode::AUTH_PASSWORD_WRONG);
               }
           }
       }else{  //手机登录
           //查看验证是否正确
           if(!$this->smsService->checkCodeValid($data['code'],$data['mobile'])) {
               return $this->fail(ResponseCode::AUTH_SMS_CODE_WRONG);
           }
          if(!$user) { //添加用户
               $user = $this->userService->store($request);
          }
       }
       $return = [
           'token' => Auth::login($user),// 生成token
           'userInfo' => [
               'role' => $user->current_role
           ]
       ];
       //用户未设置密码
      if(!$user->password) {

          return $this->codeReturn(ResponseCode::AUTH_UNSETTING_LOGIN_PWD, $return);
      }
        return $this->success($return);
   }

    /**
     * 发送短信
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
   public function regCaptcha(Request  $request)
   {
       // 获取手机号
       $mobile = $request->input('mobile');
       // 验证手机号是否合法
       if (empty($mobile)) {
           return $this->fail(ResponseCode::PARAM_ILLEGAL);
       }
       $validator = Validator::make(['mobile' => $mobile], ['mobile' => 'regex:/^1[0-9]{10}$/']);
       if ($validator->fails()) {
           return $this->fail(ResponseCode::AUTH_INVALID_MOBILE);
       }
       // 防刷验证，一分钟内只能请求一次，当天只能请求10次
       $lock = Cache::add('register_captcha_lock|'.$mobile, 1, 60);
       if (!$lock) {
           return $this->fail(ResponseCode::AUTH_CAPTCHA_FREQUENCY);
       }
       $isPass = $this->userService->checkMobileSendCaptchaCount($mobile);
       if (!$isPass) {
           return $this->fail(ResponseCode::AUTH_CAPTCHA_FREQUENCY, '验证码当天发送不能超过10次');
       }
       // 保存手机号和验证码的关系
       $this->smsService->send($mobile);
       return $this->success();
   }

    /**
     * 退出登录
     * @return \Illuminate\Http\JsonResponse
     */

   public function logout()
   {
       Auth::guard('api')->logout();
       return $this->success();

   }



}
