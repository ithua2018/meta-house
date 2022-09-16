<?php
namespace App\Services;

use App\Constants\ResponseCode;
use App\Exceptions\BusinessException;
use App\Http\Requests\Mobile\Request;
use App\Models\House;
use App\Models\InvitationRecord;

class  InvitationRecordService extends BaseService
{
    /**
     * 添加或者修改
     * @param Request $input
     * @param InvitationRecord|null $model
     * @return bool
     * @throws BusinessException
     */
    public function createOrEdit(Request $input, ?InvitationRecord $model=null)
    {
        //查询房子是否存在
        $house = House::withTrashed()->where('id', $input->house_id)->get();

        if(is_null($house)) {
            throw new BusinessException(ResponseCode::DATA_IS_NULL);
        }
        if(!is_null($model)) {
            $model->status = $input->status;
            $model->update_time = time();
        } else {
            $model = InvitationRecord::new();
            $model->add_time = time();
            $model->invite_uid = $input->user_id;
            $model->invited_uid = $input->invited_uid;
            $model->house_id = $input->house_id;
            $model->remark = $input->remark;
            $model->viewing_time = strtotime($input->viewing_date.' '.$input->viewing_time);
        }

        return $model->save();

    }

}
