<?php

namespace App\Listeners;
use App\Events\HouseCollecetionDeletedEvent;
use App\Services\Collections\HousesCollectionService;
use Illuminate\Support\Facades\Log;

class HouseCollecetionDeletedListener
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
     * @param  App\Events\HouseCollecetionDeletedEvent  $event
     * @return void
     */
    public function handle(HouseCollecetionDeletedEvent $event)
    {
       $this->housesCollectionService->remove($event->id);
    }
}
