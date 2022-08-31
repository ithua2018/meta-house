<?php
namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AbstractApiController extends Controller
{
    protected $only;
    protected $except;

    public function __construct()
    {
        $option = [];
        if (!is_null($this->only)) {
            $option['only'] = $this->only;
        }
        if (!is_null($this->except)) {
            $option['except'] = $this->except;
        }
        $this->middleware('auth:api', $option);
    }

    /**
     * @return User|null
     */
    public function user()
    {
        return Auth::guard('api')->user();
    }


    public function userId()
    {
        return $this->user()->getAuthIdentifier();
    }

    /**
     * 当前登录的角色
     * @return mixed
     */

    public function currentRole()
    {
       // $payload = auth()->getPayload()->toArray();
        return  $this->user()->current_role;
    }
    /**
     * 当前uuid
     */
    public function uuid():int
    {
       return  genUid($this->userId(), $this->currentRole());
    }

}
