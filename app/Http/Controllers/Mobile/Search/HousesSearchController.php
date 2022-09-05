<?php
namespace App\Http\Controllers\Mobile\Search;
use App\Constants\UserConstants;
use App\Http\Controllers\Mobile\AbstractApiController;
use App\Models\Collections\House as HouseCollecttion;
use App\Models\House;
use App\Services\Collections\HousesCollectionService;
use App\Services\HouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class HousesSearchController  extends  AbstractApiController
{
    private HousesCollectionService  $housesCollectionService;
    private HouseService  $houseService;
    public function __construct(
        HousesCollectionService  $housesCollectionService,
        HouseService  $houseService
    )
    {
        $this->housesCollectionService = $housesCollectionService;
        $this->houseService = $houseService;
    }

    //匹配
    public  function index(Request $request)
    {
        $q = House::query();
        $limit = $request->input('limit', 15);
        $page = $request->input('page', 1);
        $defaultCity = $request->input('defalutCity',  '');
        //深圳市-商圈-罗湖-桂圆街道
        $checkedCity = $request->input('checkedCity', '');
        $subwayPoints = $request->input('subwayPoint', '');

        if(!empty($checkedCity)) {
            //是否是地铁 如果是地铁查看10公里范围内的
            $areas = explode('-', $checkedCity);
            $isSubway =   $areas[1] === '地铁' ? true :  false;
            if(!$isSubway) {
                $township = Arr::last($areas);
                $q->where('township', $township);
            } else {
                unset($areas[1]);
                if(!empty($subwayPoints)) {
                    $lngLat = explode(',', $subwayPoints);
                    $q = $this->houseService->getNearby($lngLat, 10000);
                }
            }
        }
        $type = $request->input('type', '');
        //默认当前城市
        if($defaultCity && empty($checkedCity)) {
            $q->where('city',$defaultCity);
        }
        $user = auth('api')->user();
        if(is_null($user)) { //游客
            $role = UserConstants::USER_ROLE_TENANT;
        } else {
            $role = $user->current_role;
        }
        switch ($role) {
            case UserConstants::USER_ROLE_LANDLOARD:  //房东
                $q->where('is_owner', 0);
                //寻找需要合租 租房 买房的
                if( $type) {
                    $q->where('type',  $type);
                }
                break;
            case UserConstants::USER_ROLE_TENANT: //租房的
                $q->where('is_owner', 1)
                  ->whereIn('type', [2, 3]);
                break;
            case UserConstants::USER_ROLE_BUYER: //买房的
                $q->where('is_owner', 1)
                  ->where('type',  1);
        }
        $paginate =  $q->orderBy('add_time', 'desc')
                       //  ->where('status', 1)
                         ->paginate($limit, ['*'], 'page', $page);
        $list = $this->paginate($paginate);
        $list['list'] = $this->houseService->parseHouseData($list['list']);
        return $this->success($list);

    }

    //附近 10公里以内
    public function nearby(Request $request)
    {
        //当前的经纬度
        $current_point = $request->input('point', '');
        $limit = $request->input('limit', 15);
        $page = $request->input('page', 1);
        if(!$current_point) {
           return $this->fail();
        }
        $data = explode(',', $current_point);
        $query = $this->houseService->getNearby($data, 10000);
        $user = auth('api')->user();
        if(is_null($user)) { //游客
            $role = UserConstants::USER_ROLE_TENANT;
        } else {
            $role = $user->current_role;
        }
        switch ($role) {
            case UserConstants::USER_ROLE_LANDLOARD:  //房东
               $query->where('is_owner',0);
              break;
            case UserConstants::USER_ROLE_TENANT: //租房的
              $query->where('is_owner', 1)
                     ->whereIn('type', [2,3]);
               break;
            case UserConstants::USER_ROLE_BUYER: //买房的
                  $query->where('is_owner', 1)
                        ->where('type', 1);
        }
        $paginate = $query ->paginate($limit, ['*'], 'page', $page);
        $list = $this->paginate($paginate);
        $list['list'] = $this->houseService->parseHouseData($list['list']);
        return $this->success($list);
    }

}
