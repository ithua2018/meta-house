<?php

namespace App\Providers;

use App\Models\House;
use App\Observers\SyncToHouseCollectionObserver;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{

    /**
     * 启动所有应用程序服务。
     *
     * @return void
     */
    public function boot()
    {

        House::observe(SyncToHouseCollectionObserver::class);
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
