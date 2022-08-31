<?php


namespace App\Console\Commands\Elasticsearch;

use App\Models\Area;
use App\Models\Collections\Areas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncAreasCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'sync:areas';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步地区到ES';

    /**
     * Create a new command instance.
     *可以去掉不影响使用
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Area::chunkById(1000, function ($items) {
            // console_debug($items);
            $this->info(sprintf('正在同步第%u到%u的数据开始', $items->first()->id, $items->last()->id));

            foreach ($items as $item) {
                /** @var Area $item * */
                $arr = [
                    '_id'           => (string) $item->id,
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'pid'           => $item->pid,
                    'pinyin'        => $item->pinyin,
                    'pinyin_prefix' => $item->pinyin_prefix,
                    'ext_id'        => $item->ext_id,
                    'ext_name'      => $item->ext_name,
                    'deep'          => $item->deep
                ];
                $res = Areas::create($arr);
            }
            $this->info(sprintf('正在同步第%u到%u的数据结束', $items->first()->id, $items->last()->id));
        });

    }

}
