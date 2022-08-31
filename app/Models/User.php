<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Auth\Authorizable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * App\Models\User
 *
 * @property int $id 用户ID
 * @property string $username 用户名
 * @property string $password 密码
 * @property string $mobile 用户手机号
 * @property string $sex 0-未知 1-男 2-女
 * @property string $avatar 用户头像
 * @property string $role 角色类型 1-房东 2-租客 3-购房
 * @property string $register_time 注册时间
 * @property string $last_login_time 上次登录时间
 * @property string $last_login_ip 上次登录IP
 * @property string $login_time 当前登录时间
 * @property string $login_ip 当前登录IP
 * @property string $update_time 更新时间
 * @property string $status 是否禁用 0-正常 1-禁用
 * @property string|null $deleted_at
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRegisterTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @mixin \Eloquent
 * @property int $current_role 角色类型 1-房东 2-租客 3-购房 0-未选择
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCurrentRole($value)
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract,JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;
    const UPDATED_AT = null;
    const CREATED_AT = null;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username','password',
        'mobile','register_time',
        'login_time','login_ip'

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    // 获取用户标识（即：用户的id，对应于生成的token的payload中的sub字段）
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // payload中附加的自定义数据
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->current_role,
            'user_id'=>$this->getKey(),
            'uuid' => genUid($this->getKey(), $this->current_role)
        ];
    }




}
