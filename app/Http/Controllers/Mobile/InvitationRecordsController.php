<?php


namespace App\Http\Controllers\Mobile;


use App\Constants\ResponseCode;
use App\Http\Requests\Mobile\InvitationRecordRequest;
use App\Models\House;
use App\Repositories\HouseRepository;
use App\Repositories\InvitationRecordRepository;
use App\Services\HouseService;
use App\Services\InvitationRecordService;

class InvitationRecordsController  extends  AbstractApiController
{
    private InvitationRecordRepository  $invitationRecordRepository;
    private InvitationRecordService  $invitationRecordService;
    private HouseRepository $houseRepository;
    private HouseService $houseService;
    public function __construct(
        InvitationRecordRepository  $invitationRecordRepository,
        InvitationRecordService  $invitationRecordService,
        HouseRepository $houseRepository,
        HouseService $houseService
    )
    {
        $this->invitationRecordRepository = $invitationRecordRepository;
        $this->invitationRecordService = $invitationRecordService;
        $this->houseService = $houseService;
        $this->houseRepository = $houseRepository;
    }

    /**
     * 预约列表
     * @return array
     */
    public function list()
    {
        // return InvitationRecord::new()->get();
        //如果被软删除  find()是找不到的
         $house = House::withTrashed()->find('2417');
        // $house = House::onlyTrashed()->where('id', 2417)->get();
        return $house->toArray();
    }



    /**
     * 预约详情
     */

    public function show($id)
    {

    }

    /**
     * 邀请预约看房
     * @param InvitationRecordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function  create(InvitationRecordRequest $request)
    {
        $request->user_id = $this->uuid();
        $res = $this->invitationRecordService->createOrEdit($request);
        return $this->failOrSuccess($res);
    }

    /**
     * 更新预约看房的状态
     * @return void
     */

    public function edit(InvitationRecordRequest $request)
    {
        $request->user_id = $this->uuid();
        $model = $this->invitationRecordRepository->getFirstWhere(['id'=> $request->id, 'invite_uid' => $this->uuid()]);
        if(is_null($model)) {
            return $this->fail(ResponseCode::DATA_IS_NULL);
        }
        $res = $this->invitationRecordService->createOrEdit($request, $model);
        return $this->failOrSuccess($res);
    }

    /**
     * 预约房子的信息
     * @return void
     */
    public function house($id)
    {
        $houseInfo = $this->houseRepository->getFirstWhere(['id' => $id]);
        if(is_null($houseInfo)) {
            return $this->fail(ResponseCode::DATA_IS_NULL);
        }
        $except = ['content', 'images_show', 'facilities','images'];
        $info = $this->houseService->getSimpleInfo($houseInfo->toArray(), $except);
        return $this->success($info);
    }

    public function destory()
    {

    }

}
