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
                'icon' => 'chuang'
            ],
            [
                'id' => 2,
                'name' => '冰箱',
                'icon' => 'bingxiang'
            ],
            [
                'id' => 3,
                'name' => '空调',
                'icon' => 'kongtiao'
            ],
            [
                'id' => 4,
                'name' => '热水器',
                'icon' => 'reshuiqi'
            ],
            [
                'id' => 5,
                'name' => '洗衣机',
                'icon' => 'xiyiji'
            ],
            [
                'id' => 6,
                'name' => '燃气灶',
                'icon' => 'ranqizao'
            ],
            [
                'id' => 7,
                'name' => '沙发',
                'icon' => 'shafa'
            ],
            [
                'id' => 8,
                'name' => '桌子',
                'icon' => 'zhuozi'
            ]
        ];

        return  collect($arr)->map(function($item) {
               $item['image_selected'] = config('rent.image_url').'/'.'storage/image/facility/meta_house_'.$item['icon'].'_selected.png';
               $item['image'] = config('rent.image_url').'/'.'storage/image/facility/meta_house_'.$item['icon'].'.png';
                return $item;
        })->toArray();
    }
}
