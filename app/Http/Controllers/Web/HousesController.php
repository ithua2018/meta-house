<?php

namespace App\Http\Controllers\Web;
use App\Constants\ResponseCode;
use App\Events\HouseCollecetionCreatedEvent;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\UsersInformation;
use App\Services\AmapService;
use App\Services\EquipmentsService;
use App\Services\MyHouseService;
use Illuminate\Http\Request;

class HousesController   extends Controller
{
    private AmapService $amapService;
    private EquipmentsService $equipmentsService;
    public function __construct(
        AmapService $amapService,
        EquipmentsService $equipmentsService
    )
    {
        $this->amapService = $amapService;
        $this->equipmentsService = $equipmentsService;
    }

    //获取用户信息
    public function getUserInfo($uuid)
    {
        if(empty($uuid)) return $this->fail(ResponseCode::PARAM_ILLEGAL);
        $q = UsersInformation::query();
        $userInfo = $q->with(['User'])->where('uuid', $uuid)->first();
        if(is_null($userInfo)) {
            $userInfo = [];
        } else {
            $userInfo =  $userInfo->toArray();
        }
        return $this->success($userInfo);
    }

  //房屋列表
    public function list()
    {
        $uuid= 1301;
        $houses = House::query()->where('uuid', $uuid)->get();
        $uuids =  collect($houses)->pluck('uuid')->unique()->toArray();
        $userInfo = UsersInformation::query()->whereIn('uuid', $uuids)->get(['uuid','nick_name'])->keyBy('uuid');
        $list =  $houses->map(static function(House  $house)use($userInfo) {
            /**@var  UsersInformation $user*/
            $user = $userInfo->get($house->uuid);
            $house->publisher = $user->nick_name;
            $house->avatar_show = $user->avatar_show;
            return $house;
        })->toArray();
        return $this->success($list);
    }

    //添加出租房信息
    public function store(Request  $request)
    {


       $data = $request->only(['lat', 'lng']);
        try {
            $location = $this->amapService->getRegeo($data['lng'], $data['lat']);
            if(is_null($location)) {
                throw  new BusinessException(ResponseCode::LOCATION_INFO_ERROR, '获取定位信息失败');
            }

        }catch (Exception $e) {
            throw  new BusinessException(ResponseCode::LOCATION_INFO_ERROR, $e->getMessage());
        }
        /**@var House $houseModel*/
       $houseModel = House::new();
        $houseModel->user_id = 13;
        $houseModel->uuid =1301;
        $houseModel->is_owner = 1;
        $houseModel->type = 2;
        $houseModel->area = mt_rand(10,200);
        $houseModel->floor = mt_rand(1,26);
        $houseModel->is_elevator =  $houseModel->floor > 4 ? 1 : 0;
        $houseModel->price_range_min = 500;
        $houseModel->price_range_max = 3500;
        $houseModel->vacancy_time = time()+ mt_rand(1,20)*24*60*60;
        $houseModel->halls = random_int(0,3);
        $houseModel->rooms = random_int(1,10);
        $houseModel->facilities = '1,2,3,4,5,6';
        $houseModel->lon = $data['lng'];
        $houseModel->lat = $data['lat'];
        $houseModel->images =implode(',', ['d5559c889e72.jpg']);
        $houseModel->content = '测试数据';
        $houseModel->roommate = '';
        $houseModel->address = $location['address'];
        $houseModel->country =  $location['country'];
        $houseModel->province =  $location['province'];
        $houseModel->city  =  $location['city'];
        $houseModel->district  =  $location['district'];
        $houseModel->township =  $location['township'];
        $houseModel->is_fake = 1;
        $houseModel->add_time = time();
        $result =  $houseModel->save();
        if($result) {
           event(new HouseCollecetionCreatedEvent($houseModel));
        }
       // return response()->json($houseModel->toArray(), 200, $headers);
        return $this->success($houseModel->toArray());
    }





}
