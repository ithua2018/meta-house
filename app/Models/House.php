<?php

namespace App\Models;

use App\Observers\HouseObserver;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\House
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property string $uuid UUID
 * @property int $type 类型 1-出售 2-整租 3-合租
 * @property float $area 房屋面积
 * @property int $floor 层数
 * @property int $is_elevator 是否有电梯 0-无，1-有
 * @property string $price_range_min 最小价格
 * @property string $price_range_max 最大价格
 * @property int $vacancy_time 房屋空出时间
 * @property int $halls 几厅
 * @property int $rooms 几室
 * @property string $facilities 房屋设施 格式1,2,3,4
 * @property string $lon 经度
 * @property string $lat 维度
 * @property string $address 详细地址
 * @property string $images 图片地址,img1|img2
 * @property string $content 介绍
 * @property string $roommate 室友 json数组[{num:1,sex:女,month:12}]
 * @property int $status 状态 0-正常 1-已出租或者已售出
 * @property int $views 浏览次数
 * @property int $add_time 发布时间
 * @property int $update_time 更新时间
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|House newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|House newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|House query()
 * @method static \Illuminate\Database\Eloquent\Builder|House whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereFacilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereFloor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereHalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereIsElevator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereLon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House wherePriceRangeMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House wherePriceRangeMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereRoommate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereRooms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereVacancyTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|House whereViews($value)
 * @mixin \Eloquent
 */
class House extends BaseModel
{
 protected $hidden = [
    'vacancy_time',
     'halls',
     'rooms',
     'is_fake',
     'status',
     'deleted_at',
   // 'images'
 ];
 protected $appends = ['vacancy_time_show', 'images_show', 'tags_show'];

 //空出房间的时间
 public function getVacancyTimeShowAttribute()
 {
     if(isset($this->attributes['vacancy_time'])) {
         return date('Y-m-d', $this->attributes['vacancy_time']);
     }

 }
 //处理图片显示
    public function getImagesShowAttribute()
    {
        if(isset($this->attributes['images'])) {
            if(!empty($this->attributes['images'])) {
               $arr =  explode(',',$this->attributes['images']);
              return  array_map(function($item) {
                  //false !== strpos($item, '/storage/image/')
                  if(false !== strpos($item, '/storage/image/')) {
                      return config('rent.image_url').$item;
                  } else {
                      return config('rent.image_url').'/storage/image/fake/'.$item;
                  }

               }, $arr);
            }
        }

    }
    public function getTagsShowAttribute()
    {
        if(isset($this->attributes['tags'])) {
           return explode(';', $this->attributes['tags']);
        }

    }

}
