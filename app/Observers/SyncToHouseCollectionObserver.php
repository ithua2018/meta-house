<?php

namespace App\Observers;

use App\Models\House;
use Illuminate\Support\Facades\Log;

class SyncToHouseCollectionObserver
{
    /**
     * Handle the house "created" event.
     *
     * @param  \App\Models\House  $dummyModel
     * @return void
     */
    public function created(House $dummyModel)
    {
      // var_dump(33333);
        console_debug('我是观察者1号');
        Log::info('我是观察者1号');
    }

    /**
     * Handle the house "updated" event.
     *
     * @param  \App\Models\House  $dummyModel
     * @return void
     */
    public function updated(House $dummyModel)
    {
        console_debug('我是观察者2号');
        Log::info('我是观察者2号');
    }

    /**
     * Handle the house "deleted" event.
     *
     * @param  \App\Models\House  $dummyModel
     * @return void
     */
    public function deleted(House $dummyModel)
    {
        //
    }

    /**
     * Handle the house "restored" event.
     *
     * @param  \App\Models\House  $dummyModel
     * @return void
     */
    public function restored(House $dummyModel)
    {
        //
    }

    /**
     * Handle the house "force deleted" event.
     *
     * @param  \App\Models\House  $dummyModel
     * @return void
     */
    public function forceDeleted(House $dummyModel)
    {
        //
    }
}
