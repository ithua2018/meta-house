<?php

namespace App\Models;
use App\Constants\UserConstants;
use Eloquent;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\UsersInformation
 *
 * @property int $id 用户信息ID
 * @property int $user_id 用户ID
 * @property string $role 角色类型 1-房东 2-租客 3-购房
 * @property int $uuid
 * @property string $nick_name 昵称
 * @property string $true_name 真实姓名
 * @property string $sex 0-未知 1-男 2-女
 * @property string $avatar 用户头像
 * @property string $label 用户标签
 * @property int $add_time 添加时间
 * @property int $update_time 更新时间
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereNickName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereTrueName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersInformation whereUuid($value)
 * @mixin Eloquent
 */
class UsersInformation extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','role','uuid', 'nick_name', 'sex', 'avatar', 'label'
    ];
    protected  $hidden = ['deleted_at'];
    protected $appends = ['sex_show', 'avatar_show', 'label_show', 'role_name'];
    public function getSexShowAttribute($value){
        if(isset($this->attributes['sex'])){
            if($this->attributes['sex'] == UserConstants::USER_GENDER_UNKNOWN){
                return '未知';
            }else if($this->attributes['sex'] == UserConstants::USER_GENDER_MAN){
                return '男';
            }else if($this->attributes['sex'] == UserConstants::USER_GENDER_WOMAN){
                return '女';
            }
        }

    }
    public function getRoleNameAttribute() {
        if(!empty($this->attributes['role'])) {
            return UserConstants::ROLE_MAPPING_NAME[$this->attributes['role']];
        }
        return '';
    }
    public function getLabelShowAttribute() {
        if(!empty($this->attributes['label'])){
            return  explode(',', $this->attributes['label']);
        }
        return [];
    }

    public function getAvatarShowAttribute() {
        if(!empty($this->attributes['avatar'])){
            return config('rent.image_url').'/'.trim($this->attributes['avatar'], '/');
        }
        return config('rent.image_url').config('rent.default_user_avatar');
    }
    //用户组
    public function User() {
      //  return $this->hasOne(User::class, 'id', 'user_id');
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
