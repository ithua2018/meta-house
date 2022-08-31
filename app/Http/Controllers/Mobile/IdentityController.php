<?php

namespace App\Http\Controllers\Mobile;

use App\Constants\ResponseCode;
use App\Http\Requests\Mobile\IdentityRequest;
use App\Models\User;
use App\Repositories\UsersInformationRepository;
use App\Services\IdentityService;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;


class IdentityController extends AbstractApiController
{
    private UsersInformationRepository $usersInformationRepository;
    private IdentityService  $identityService;
    public function __construct(
        UsersInformationRepository $usersInformationRepository,
        IdentityService  $identityService
    )
    {
        $this->usersInformationRepository = $usersInformationRepository;
        $this->identityService = $identityService;
    }

    /**
     * 身份列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        //角色类型 1-房东 2-租客 3-购房 0-未选择
        $data = [
            ['id' => 1, 'name' => '房东'],
            ['id' => 2, 'name' => '租客'],
            ['id' => 3, 'name' => '购房']
        ];

        return $this->success($data);
    }

    /**
     * 选择身份
     * @param IdentityRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */

    public function checked(IdentityRequest $request)
    {
        $role = $request->id;
        $result = $this->identityService->update($role, $this->user());

        if(!$result) {
           return $this->fail(ResponseCode::AUTH_SELECT_ROLE_FAIL);
        }
        //刷新token  不会刷新payload
        $token = auth()->refresh();
        $return = [
            'token' => $token,// 生成token
            'userInfo' => [
                'role' => $this->user()->current_role
            ]
        ];
        return $this->success($return);
    }
}
