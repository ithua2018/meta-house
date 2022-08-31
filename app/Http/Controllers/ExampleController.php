<?php



namespace App\Http\Controllers;

use App\Jobs\TestQueue;
use App\Models\Collections\FakeImages;
use App\Services\AmapService;
use App\Services\EquipmentsService;
use Barryvdh\Debugbar\Facades\Debugbar;
use Bschmitt\Amqp\Facades\Amqp;
use Illuminate\Http\Request;

class ExampleController extends Controller
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

    public function  test()
    {
        $result = FakeImages::raw(function($collection){ return $collection->aggregate([ ['$sample' => ['size' => 3]] ]); });
        return $result;
        $imageModel = new FakeImages();
        $count = $imageModel->count();
        $images = FakeImages::take(3)->skip(rand(0,$count-1))->get()->toArray();
        return $images;
        $arr = [];
        $imageModel->id = 1;
        if($count>0) {
            $imageModel->id = $count+1;
        }
        $imageModel->image_url = 'test.png';
        $res = $imageModel->save();
//        $arr =  $this->equipmentsService->getList();

         return  $count;
    }

    public function store_with_mq(Request $request)
    {
        Debugbar::error('Error!');
        $str = 'a:18:{s:9:"member_id";i:135651;s:4:"area";s:36:"Sen Sok District,Phnom Penh,Cambodia";s:7:"address";s:89:"Sen Sok District,Phnom Penh,Cambodia 1003Khan Sensok,Sen Sok District,Phnom Penh,Cambodia";s:18:"to_delivery_remark";s:0:"";s:10:"address_id";i:129568;s:13:"address_label";r:5;s:7:"area_id";i:0;s:24:"to_delivery_position_pic";r:5;s:9:"mob_phone";s:8:"69580616";s:6:"points";s:20:"104.885993,11.602466";s:9:"true_name";s:9:"anonymous";s:11:"province_id";i:85918;s:5:"phone";s:7:"+580616";s:6:"street";s:52:"1003Khan Sensok,Sen Sok District,Phnom Penh,Cambodia";s:9:"area_info";s:37:"Sen Sok District,Phnom Penh,Cambodia ";s:9:"tel_phone";N;s:10:"country_id";i:85917;s:7:"city_id";i:85926;}';

        $arr = pro_unserialize($str);
        return $arr;

//        $attributes = [
//            'user_id' => rand(0,99999),
//            'user_name' => 'ithua_'.rand(0,99999)
//        ];
//        // 加入mq队列中
//        //$this->dispatch(new TestQueue($attributes));
//        Amqp::publish('', json_encode($attributes) , ['queue' => 'test']);
//        return $attributes;
       // return resp(Code::CreatePostsSuccess, Msg::CreatePostsSuccess);
    }
}

