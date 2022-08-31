<?php

namespace App\Listeners;

use App\Events\HouseCollecetionCreatedEvent;
use App\Services\Collections\HousesCollectionService;
use Illuminate\Support\Facades\Log;
class HouseCollecetionCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    private HousesCollectionService $housesCollectionService;
    public function __construct(
        HousesCollectionService $housesCollectionService
    )
    {
        $this->housesCollectionService = $housesCollectionService;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\HouseCollecetionCreatedEvent  $event
     * @return void
     */
    public function handle(HouseCollecetionCreatedEvent $event)
    {
       // Log::info('监听创建',$event->house->toArray());
        $arr = $event->house->toArray();
        $this->housesCollectionService->add($arr);

    }
}
