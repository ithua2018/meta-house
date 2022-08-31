<?php

namespace App\Http\Controllers\Mobile;

use App\Constants\AreaConstants;
use App\Constants\ResponseCode;
use App\Http\Requests\Mobile\AreaRequest;
use App\Http\Requests\Mobile\IdentityRequest;
use App\Models\Area;
use App\Repositories\AreaRepository;
use App\Repositories\SubwayStationRepository;
use App\Services\AmapService;
use Illuminate\Http\Request;
use Predis\Command\Redis\SUBSCRIBE;


class AreaController extends AbstractApiController
{
    private AreaRepository $areaRepository;
    private  AmapService  $amapService;
    private SubwayStationRepository $subwayStationRepository;
    public function __construct(
        AreaRepository $areaRepository,
        AmapService  $amapService,
        SubwayStationRepository $subwayStationRepository
    )
    {
        $this->areaRepository = $areaRepository;
        $this->amapService = $amapService;
        $this->subwayStationRepository = $subwayStationRepository;

    }

    /**
     * 获取城市
     * @return array
     */
    public function getCities()
    {
        $cities = $this->areaRepository->getAreaLevel1();
        $arr = $cities->groupBy('pinyin_prefix')->toArray();
         ksort($arr);
         $retrun_data = [];
         $i = 0;
        foreach ($arr as $k => $val) {
            $retrun_data[$i] =  [
                'group_id' => $i+1,
                'group_name' =>  strtoupper($k),
                'items' => $val
            ];
           $i++;
        }

        return $this->success($retrun_data);
    }

    /**
     * 获取商圈或者地铁
     */
    public function getBdSw(AreaRequest $request)
    {
        $data = $request->only(['id', 'type']);
        $arr = [];
        //商圈
        if($data['type'] == AreaConstants::BUSINESS_DISTRICT) {
            $twos = $this->areaRepository->getAreaLevel2ByPid($data['id']);
            $threes = $this->areaRepository->getAreaLevel3();
            $arr = $twos->map(static function($two)use($threes) {
                /**@var Area $two**/
                 $childs = $threes->get($two->id);
                $children = $childs->map(static function($child){
                    /**@var Area $child**/
                    return [
                        'id' => $child->id,
                        'name' => $child->ext_name,
                        'point' => ''
                    ];
                });
                return  [
                    'id' => $two->id,
                    'name' => $two->ext_name,
                    'children' => $children
                ];
            });
           $arr = $arr->toArray();
        } else { //地铁
            $arr2 =  $this->subwayStationRepository->getOpenLines($data['id'])->groupBy('line')->toArray();
            if(!empty($arr2)) {
                foreach($arr2 as $k => $val)  {
                    $children = [];
                    foreach ($val as $kk => $v) {
                        $children[$kk] = [
                            'id'=>$v['id'],
                            'name' => $v['name'],
                            'point' => $v['lon'].','.$v['lat']
                        ];
                    }
                    $arr[] = [
                        'id' => findNum($k)?? '0',
                        'name' => $k,
                        'children' =>  $children
                    ];

                }
            }
          $arr =  collect($arr)->sortBy('id')->toArray();
        }
        return $this->success(array_values($arr));
    }


}
