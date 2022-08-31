<?php

namespace App\Console\Commands;

use App\Models\Collections\Areas;
use App\Services\StoreService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'test_command';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Command description';

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
      $areas = DB::table('areas')->where(['id' =>130303])->first();
      $arr = collect($areas)->toArray();
      $arr['_id'] = (string)$arr['id'];
       $res = Areas::create($arr);
        var_dump($res);
    }


}
