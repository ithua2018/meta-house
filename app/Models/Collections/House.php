<?php
namespace App\Models\Collections;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class House extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'houses_collection';
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $hidden = [
        'vacancy_time',
        'halls',
        'rooms',
        'is_fake',
        'status',
        'deleted_at',
        'rent_price',
        'url_id',
        'add_time',
        'update_time'
    ];

    protected $appends = ['vacancy_time_show', 'images_show', 'tags_show', 'publish_time'];

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

    public function getPublishTimeAttribute()
    {
        if(isset($this->attributes['add_time'])) {
            return Carbon::parse($this->attributes['add_time'])->format('Y-m-d H:i:s');
        }

    }

}
