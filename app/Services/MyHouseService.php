<?php

namespace App\Services;
use App\Constants\HouseConstants;
use App\Constants\ResponseCode;
use App\Events\HouseCollecetionCreatedEvent;
use App\Events\HouseCollecetionUpdatedEvent;
use App\Exceptions\BusinessException;
use App\Http\Requests\Mobile\Request;
use App\Models\House;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\Collections\House as HouseCollection;
class MyHouseService  extends BaseService
{
    private AmapService $amapService;
    public function __construct(
        AmapService $amapService
    )
    {
        $this->amapService = $amapService;
    }

    /**
     * 创建或者修改
     * @param Request $request
     * @param House|null $houseModel
     * @return bool
     * @throws BusinessException
     */
    public function createOrUpdate(Request $request, ?House $houseModel=null):?bool
    {
        try {
            $location = $this->amapService->getRegeo($request->lon, $request->lat);
            if(is_null($location)) {
                throw  new BusinessException(ResponseCode::LOCATION_INFO_ERROR, '获取定位信息失败');
            }

        }catch (Exception $e) {
               throw  new BusinessException(ResponseCode::LOCATION_INFO_ERROR, $e->getMessage());
        }
        $update = false;
       if(!is_null($houseModel)) {
           $houseModel->update_time = time();
           $update = true;
       } else {
           $houseModel = House::new();
           $houseModel->user_id = $request->user_id;
           $houseModel->uuid = $request->uuid;
           $houseModel->is_owner = $request->is_owner;
           $houseModel->add_time = time();
       }
       $houseModel->type = $request->type;
       $houseModel->area = $request->area;
      // $houseModel->floor = $request->floor;
       $houseModel->is_elevator = $request->is_elevator;
       $houseModel->price_range_min = $request->price_range_min;
       $houseModel->price_range_max = $request->price_range_max;
       $houseModel->vacancy_time = strtotime($request->vacancy_time);
//       $houseModel->halls = $request->halls;
//       $houseModel->rooms = $request->rooms;
        $houseModel->house_structure = $request->house_structure;
        $houseModel->lease_type = $request->lease_type;
        $houseModel->lease_aging = $request->lease_aging;
       $houseModel->facilities = $request->facilities;
       $houseModel->lon = $request->lon;
       $houseModel->lat = $request->lat;
       $houseModel->address = $request->address;
       if(!empty( $request->images)) {
          $images =  explode(',', $request->images);
          $images = array_map(function($item) {
              return imgPathShift('house',  $item);
          },$images);
           $houseModel->images =implode(',', $images);
       }
       $houseModel->title = $request->title;
       $houseModel->content = $request->desc;
       $houseModel->address_extra = $request->address_extra;
       $houseModel->roommate = $request->roommate;
       $houseModel->limit_people_number = $request->limit_people_number;
       $houseModel->address = $location['address'];
       $houseModel->country =  $location['country'];
       $houseModel->province =  $location['province'];
       $houseModel->city  =  $location['city'];
       $houseModel->district  =  $location['district'];
       $houseModel->township =  $location['township'];
       $result =  $houseModel->save();
       if($result) {
           $update ? event(new HouseCollecetionUpdatedEvent($houseModel)) : event(new HouseCollecetionCreatedEvent($houseModel));
       }
       return $result;
    }

    /**
     * 分页
     * @param $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listHouses($request, $user_id):LengthAwarePaginator
    {
        $query = House::query()->where('uuid', $user_id);
        switch ($request->type) {
            case HouseConstants::HOUSES_STATE_ON_SHELF:
                $query->where('status', 1);
                break;
            case HouseConstants::HOUSES_STATE_OFF_SHELF:
                $query->where('status', 0);
                break;
        }
        return $query->orderBy('add_time', 'desc')
                     ->paginate($request->limit, '*', 'page', $request->page);
    }


}
