<?php
namespace  App\Http\Controllers\Mobile;

use App\Common\RedisLock;
use App\Common\RedisService;
use App\Constants\ResponseCode;
use App\Http\Requests\Mobile\RegPwdRequest;
use App\Http\Requests\Mobile\UpdateUserRequest;
use App\Repositories\UserRepository;
use App\Repositories\UsersInformationRepository;
use Illuminate\Hashing\HashManager;


class UserController extends  AbstractApiController
{
    private UserRepository $userRepository;
    private HashManager $hash;
    private UsersInformationRepository $usersInformationRepository;
    public function __construct(
        UserRepository $userRepository,
        HashManager $hash,
        UsersInformationRepository $usersInformationRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->hash = $hash;
        $this->usersInformationRepository = $usersInformationRepository;

    }

    /**
     * 设置登录密码
     * @param RegPwdRequest $request
     * @return \Illuminate\Http\JsonResponse
     */

   public function regPwd(RegPwdRequest $request)
   {
       if($request->password !== $request->confirm_password){
         return  $this->fail(ResponseCode::AUTH_NOT_SAME_PWD);
       }
      $data['password'] = $this->hash->make($request->password);
      $result =  $this->user()->update($data);
      return $this->failOrSuccess($result);
   }

    /**
     * 展示用户资料
     * @return \Illuminate\Http\JsonResponse
     */
   public function show()
   {
       $userInfo = $this->usersInformationRepository->getFirstWhere(['user_id'=>$this->userId(), 'role'=>$this->currentRole()]);
       $userInfo['mobile'] = $this->user()['mobile'];
       return $this->success($userInfo);
   }

    /**
     * 更新资料
     * @param UpdateUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest  $request)
    {
        $redis = new RedisService();
        $columns = ['nick_name', 'sex', 'avatar', 'label'];
        $data = $request->only($columns);
        $lock = RedisLock::lock($redis, 'lock|edit_user');
        if($lock) {
            $userInfo = $this->usersInformationRepository->getFirstWhere(['user_id'=>$this->userId(), 'role'=>$this->currentRole()]);
            $data['update_time'] = time();
            if($request->avatar) {
                $data['avatar'] = imgPathShift('user', $request->avatar);
            }

            $result = $userInfo->update($data);
            RedisLock::unlock($redis, 'lock|edit_user');
            return $this->failOrSuccess($result);
        } else {
           return $this->fail(ResponseCode::CODE_SYSTEM_BUSY);
        }

    }



   /**
    * 重置密码
    */

   public function resetPwd()
   {

   }





}
