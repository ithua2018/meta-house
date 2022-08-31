<?php

namespace App\Console;


use App\Console\Commands\Elasticsearch\SyncAreasCommand;
use App\Console\Commands\SyncSubWayLinesCommand;
use App\Console\Commands\TestCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //测试
        TestCommand::class,
        //同步地铁信息
        SyncSubWayLinesCommand::class,
        SyncAreasCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
