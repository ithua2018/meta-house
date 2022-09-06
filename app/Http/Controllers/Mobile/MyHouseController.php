<?php
namespace App\Http\Controllers\Mobile;

use App\Cache\Repository\LockRedis;
use App\Constants\ResponseCode;
use App\Constants\UserConstants;
use App\Events\HouseCollecetionDeletedEvent;
use App\Http\Requests\Mobile\MysearchHousesRequset;
use App\Http\Requests\Mobile\SubmitHouseRequest;
use App\Models\House;
use App\Repositories\HouseRepository;
use App\Services\EquipmentsService;
use App\Services\HouseService;
use App\Services\MyHouseService;

class MyHouseController extends AbstractApiController
{
    private HouseRepository $houseRepository;
    private  MyHouseService $houseService;
    private HouseService  $house;
    private EquipmentsService  $equipmentsService;
    private LockRedis  $lockRedis;
    public function __construct(
        HouseRepository $houseRepository,
        MyHouseService $houseService,
        LockRedis  $lockRedis,
        EquipmentsService  $equipmentsService,
        HouseService  $house
    )
    {
        $this->houseRepository = $houseRepository;
        $this->houseService = $houseService;
        $this->lockRedis = $lockRedis;
        $this->equipmentsService = $equipmentsService;
        $this->house =  $house;
    }
    /**
     * 列表
     */
   public function index(MysearchHousesRequset  $requset)
   {

        $list  = $this->houseService->listHouses($requset, $this->uuid());
        $arr = $this->paginate($list);
        return $this->success($arr);
   }

    /**
     * 详情
     */
   public function show($id)
   {
       $model = $this->houseRepository->getFirstWhere(['id'=>$id, 'uuid'=>$this->uuid()]);
       if(is_null($model)) {
           return $this->fail(ResponseCode::DATA_IS_NULL);
       }
       $detail = $this->house->parseHouseData($model->toArray());
       return $this->success($detail);
     return $this->success($model->toArray());
   }

    /**
     * 创建
     */
   public function store(SubmitHouseRequest  $request)
   {
      $request->user_id = $this->userId();
      $request->uuid = $this->uuid();
      $request->is_owner = $this->user()->current_role == UserConstants::USER_ROLE_LANDLOARD ?  1 : 0 ;
      $result = $this->houseService->createOrUpdate($request);
      return $this->failOrSuccess($result);
   }

    /**
     * 修改
     */
   public function update(SubmitHouseRequest  $request)
   {
        $model = $this->houseRepository->getFirstWhere(['id'=>$request->id, 'uuid'=>$this->uuid()]);
        if(is_null($model)) {
            return $this->fail(ResponseCode::DATA_IS_NULL);
        }
       $result =  $this->houseService->createOrUpdate($request, $model);
       return $this->failOrSuccess($result);
   }

    /**
     * 删除
     * @param $id
     */
   public function destory($id) {
      if(!$id) {
          return $this->fail(ResponseCode::PARAM_VALUE_ILLEGAL);
      }
      $lock = $this->lockRedis->lock('myHouse', 2);
      if($lock) {
        House::where('uuid', $this->uuid())->where('id', $id)->delete();
        event(new HouseCollecetionDeletedEvent($id));
          //  $this->lockRedis->delete('myHouse');
        return $this->success('删除成功');
      } else {
        return $this->fail(ResponseCode::CODE_SYSTEM_BUSY);
      }
   }
    /**
     * 是否上架
     */
    public function changeStatus($id)
    {
        $model = $this->houseRepository->getFirstWhere(['id'=>$id, 'uuid'=>$this->uuid()]);
        if(is_null($model)) {
            return $this->fail(ResponseCode::DATA_IS_NULL);
        }
        $model->status = $model->status == 1 ? 0 : 1;
        $result =  $model->save();
        if($result) {
            event(new HouseCollecetionUpdatedEvent($model));
        }
        return  $this->failOrSuccess($result);
    }



   /**
    * 设备列表
    */

   public function  equipments()
   {
       $arr = $this->equipmentsService->getList();
       return $this->success($arr);
   }

}
