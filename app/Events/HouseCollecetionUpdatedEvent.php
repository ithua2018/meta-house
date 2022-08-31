<?php

namespace App\Events;

use App\Models\House;

class HouseCollecetionUpdatedEvent extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $house;
    public function __construct(House $house)
    {
       $this->house = $house;

    }
}
