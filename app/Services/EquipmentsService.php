<?php

namespace App\Services;




use Illuminate\Support\Arr;

class  EquipmentsService extends BaseService
{
    //获取列表
    public function getList()
    {
        $arr =  [
            [
                'id' => 1,
                'name' => '厨房',
                'icon' => 'chufang'
            ],
            [
                'id' => 2,
                'name' => '卫生间',
                'icon' => 'weishengjian'
            ],
            [
                'id' => 3,
                'name' => '冰箱',
                'icon' => 'bingxiang'
            ],
            [
                'id' => 4,
                'name' => '空调',
                'icon' => 'kongtiao'
            ],
            [
                'id' => 5,
                'name' => '热水器',
                'icon' => 'reshuiqi'
            ],
            [
                'id' => 6,
                'name' => '洗衣机',
                'icon' => 'xiyiji'
            ],
            [
                'id' => 7,
                'name' => '燃气灶',
                'icon' => 'reqizhao'
            ],
            [
                'id' => 8,
                'name' => '沙发',
                'icon' => 'shaofa'
            ],
            [
                'id' => 9,
                'name' => '床',
                'icon' => 'chuang'
            ],
            [
                'id' => 10,
                'name' => '桌子',
                'icon' => 'zhuozi'
            ]
        ];

        return  collect($arr)->map(function($item) {
               $item['image'] = config('rent.image_url').'/'.'storage/image/facility/meta_house_'.$item['icon'].'.png';
                return $item;
        })->toArray();
    }
}
