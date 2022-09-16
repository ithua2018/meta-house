<?php

namespace App\Models;
/**
 * App\Models\InvitationRecords
 *
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord query()
 * @mixin \Eloquent
 * @property int $id ID
 * @property int $invite_user_id 邀请人
 * @property int $invited_user_id 受邀人
 * @property int $house_id 房屋ID
 * @property int $viewing_time 看房时间
 * @property string|null $remark 备注
 * @property int $is_agree 是否同意 1-同意 0-不同意
 * @property int $add_time 添加时间
 * @property int $update_time 更新时间
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereInviteUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereInvitedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereIsAgree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereViewingTime($value)
 * @property int $invite_uid 邀请人
 * @property int $invited_uid 受邀人
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereInviteUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvitationRecord whereInvitedUid($value)
 */
class InvitationRecord extends  BaseModel
{

    public function house()
    {

        $this->hasOne(House::class, 'id', 'house_id');
    }
}
