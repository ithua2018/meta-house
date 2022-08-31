<?php


namespace App\Console\Commands;


use App\Models\SubwayStation;
use App\Repositories\SubwayStationRepository;
use App\Services\AmapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSubWayLinesCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'sync:sub_way_lines';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步地铁线路入库';

     private AmapService $amap;
     private SubwayStationRepository $subwayStationRepository;
    /**
     * Create a new command instance.
     *可以去掉不影响使用
     * @return void
     */
    public function __construct(
        AmapService $amap,
        SubwayStationRepository $subwayStationRepository)
    {
        parent::__construct();
        $this->amap = $amap;
        $this->subwayStationRepository = $subwayStationRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//
        $city_map_lines =  file_get_contents(storage_path().'/temp/adcode_map_city.json');
        $arr = json_decode($city_map_lines, true);
//        $arr[0] = [
//            "city_id"=> "4403",
//            "adcode"=> "4403",
//             "city_name" => '深圳'
//        ];

        foreach($arr as $k => $v) {
            $this->info(sprintf('正在同步%s市的地铁数据开始',$v['city_name']));
            for($i=1; $i<=10; $i++) {
               $list =  $this->amap->getSubwayStation($v, $i);
                $this->info(sprintf('正在同步%s市的地铁数据,第%u页数据开始',$v['city_name'], $i));
               if(empty($list)) {
                   break;
               } else {
                   foreach($list as $item) {
                       /**@var SubwayStation|null $model **/
                     $model = $this->subwayStationRepository->getFirstWhere(['id'=>$item['id']]);
                     if(!is_null($model)) {
                           $model->is_finished = $item['is_finished'];
                           $model->save();
                     } else {
                        $res =  DB::table('subway_stations')->insert($item);
                     }

                   }
               }
                $this->info(sprintf('正在同步%s市的地铁数据,第%u页数据结束',$v['city_name'], $i));
               sleep(1);
            }
            $this->info(sprintf('正在同步%s市的地铁数据结束',$v['city_name']));
        }
    }
}
