<?php


namespace App\Console\Commands;

use App\Models\House;
use App\Services\Collections\HousesCollectionService;
use App\Services\HouseService;
use App\Services\Map\AmapService;
use App\Services\Map\BaiduMapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncLianjiaDataCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'sync:lianjia';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步链家数据入库';
    private AmapService $amapService;
    private BaiduMapService $baiduMapService;
    private HouseService $houseService;
    private HousesCollectionService $housesCollectionService;
    /**
     * Create a new command instance.
     *可以去掉不影响使用
     * @return void
     */
    public function __construct(
        AmapService $amapService,
        BaiduMapService $baiduMapService,
        HouseService $houseService,
        HousesCollectionService $housesCollectionService
    )
    {
        parent::__construct();
        $this->amapService = $amapService;
        $this->baiduMapService = $baiduMapService;
        $this->houseService = $houseService;
        $this->housesCollectionService = $housesCollectionService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        DB::table('houses')->orderBy('id')->chunk(100, function($items){
//            foreach ($items as $k => $item) {
//                sleep(1);
//                $this->housesCollectionService->add(collect($item)->toArray());
//            }
//
//        });
//        var_dump('success');
//        return;
        $price_rand_group = $this->houseService->priceRangeGroup();

        DB::table('lianjia_houses')->orderBy('url_id')->chunk(100, function($items)use ($price_rand_group){
            $houseModel = House::new();

            $arr = [];
            foreach ($items as $k => $item) {
                //如果存在则跳出 继续下一个
                $res = $houseModel::where('url_id', $item->url_id)->first();
                if(!empty($res))  {
                    var_dump('已存在，下一个');
                    continue;
                }
                if($item->region == '罗湖区') {//绑定在 5301
                    $user_id = 53;
                    $uuid = 5301;
                } elseif($item->region == '宝安区') { //绑定在101
                    $user_id = 1;
                    $uuid = 101;
                } else { //绑定在 5401
                    $user_id = 54;
                    $uuid =  5401;
                }
                $title = $item->position;
                $area = $item->area;
                if(false !== strpos($area, '-')) {
                    $area = explode('-', $area)[0];
                }
                if (preg_match("/[\x7f-\xff]/", $area)) {
                   $area = rand(8,100);
                    $title = $item->area;
                }

                $min = 0;
                $max = 0;
                $rent = $item->rent;
                if(false !== strpos($rent, '-')) {
                    $rent = explode('-', $rent)[0];
                }

                $rent_price = floatval($rent);
                if(!empty($rent_price)) {
                    foreach ($price_rand_group as $val) {
                        if($val['max'] >= $rent_price && $rent_price>=$val['min']) {
                            $min = $val['min'];
                            $max = $val['max'];
                        }
                    }
                }

                //house_type
                $patterns = "/\d+/";
                preg_match_all($patterns,$item->house_type,$house_types);
                $house_types = $house_types[0] ?: [] ;
                if(!empty($house_types)) {
                    $house_type = $house_types[0].'-'.$house_types[1].'-1-'.$house_types[2];
                } else {
                    $house_type = '1-1-1-1';
                }

                $images = explode(',',  $item->local_image);
                $images = array_map(function($v) {
                    return str_replace('full', 'houses', $v);
                }, $images);
                //通过经纬度获取地址
                $bdAddress = $this->baiduMapService->getRegeo($item->longitude, $item->latitude);
                if($bdAddress) {
                    //地址通过高德API获取经纬度
                    $amapAddress = $this->amapService->getGeo($bdAddress['address']);
                }
                sleep(2);
                $detail = $item->detail;
                if($detail) {
                    $detail = str_replace('<br />', '\n', $detail);
                }
                $arr[] = [
                    'user_id' => $user_id,
                    'uuid' => $uuid,
                    'is_owner' => 1,
                    'type' => $item->lease_method == '合租'  ? 3 : 2,
                    'area' => $area,
                    'is_elevator' =>  random_int(0,1),
                    'price_range_min' => $min,
                    'price_range_max' => $max,
                    'rent_price' => $rent,
                    'vacancy_time' => time()+ mt_rand(1,20)*24*60*60,
                    'facilities' => '1,2,3,4,5,6',
                    'lease_type' => 3,
                    'lease_aging' => 12,
                    'house_structure' =>  $house_type,
                    'content' => $detail,
                    'title' => $title,
                    'images' => implode(',', $images),
                    'tags' => $item->tags,
                    'url_id' => $item->url_id,
                    'is_fake' => 1,
                    'lon' => $amapAddress[0],
                    'lat' => $amapAddress[1],
                    'address' => $bdAddress['address'],
                    'township' => $bdAddress['township'],
                    'city' => $bdAddress['city'],
                    'province' => $bdAddress['province'],
                    'country' => $bdAddress['country'],
                    'business' => $bdAddress['business'],
                    'add_time' => time()- mt_rand(1,90)*24*60*60,
                    'limit_people_number' => random_int(1, 5)
                ];
            }
            $return = DB::transaction(function () use ($arr, $houseModel) {
                $houseModel->insert($arr);
                return 1;
            }, 5);
            if ($return == 1) {
                foreach($arr as $item) {
                    $this->housesCollectionService->add($item);
                }
                var_dump('success');
            } else {
                var_dump('fail');
            }


        });
    }
}
