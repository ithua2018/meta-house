<?php


namespace App\Services;


use App\Models\Collections\House as HouseCollecttion;
use App\Models\House;
use App\Models\UsersInformation;
use Illuminate\Support\Arr;
use Jenssegers\Mongodb\Query\Builder as QueryBuilder;
class HouseService extends  BaseService
{
    /**
     * 查找附近
     * @param  array  $points
     * @param  float  $distance
     *
     * @return mixed
     */
    public function getNearby(array $points,  float $distance)
    {
        $query =       HouseCollecttion::where('location', 'near', [
            '$geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    (float)$points[0],
                    (float)$points[1],
                ],
            ],
            '$maxDistance' => $distance
        ]);
        return $query;
    }

    /**
     * 解析房屋数据
     */
    public function parseHouseData($arr)
    {
        if(empty($arr)) {
            return [];
        }
        $isMulti = true;
        if(Arr::isAssoc($arr)) {
            $arr = Arr::wrap($arr);
            $isMulti = false;
        }
        $collect = collect($arr);
        $uuids =  collect($arr)->pluck('uuid')->unique()->toArray();

        $userInfo = UsersInformation::query()->whereIn('uuid', $uuids)->get(['uuid','nick_name'])->keyBy('uuid');
        $list =   $collect->map(static function($item)use($userInfo) {
            /**@var  UsersInformation $user*/
            $user = $userInfo->get($item['uuid']);
            $item['publisher'] = $user->nick_name??'';
            $item['avatar_show'] = $user->avatar_show??'';
            unset($item['_id']);
            return $item;
        })->toArray();
        if(!$isMulti) {
            return $list[0];
        }
        return $list;
    }

    /**
     * 租房金额范围
     */
    public function priceRangeGroup()
    {

        return [

            [
                'min' => 100,
                'max' => 1000
            ],
            [
                'min' => 1000,
                'max' => 2000
            ],
            [
                'min' => 2000,
                'max' => 5000
            ],
            [
                'min' => 5000,
                'max' => 10000
            ],
            [
                'min' => 10000,
                'max' => 30000
            ],

            [
                'min' => 30000,
                'max' => 100000
            ],
            [
                'min' => 0,
                'max' => 0
            ]

        ];
    }


}
