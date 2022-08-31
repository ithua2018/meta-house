<?php

namespace App\Listeners;
use App\Events\HouseCollecetionUpdatedEvent;
use App\Services\Collections\HousesCollectionService;
use Illuminate\Support\Facades\Log;
class HouseCollecetionUpdatedListener
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
     * @param  App\Events\HouseCollecetionUpdatedEvent;  $event
     * @return void
     */
    public function handle(HouseCollecetionUpdatedEvent $event)
    {
       $arr = $event->house->toArray();
       unset($arr['id']);

       $this->housesCollectionService->modify($event->house->id, $arr);
    }
}
