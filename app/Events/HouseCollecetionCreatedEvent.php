<?php

namespace App\Events;

use App\Models\House;

class HouseCollecetionCreatedEvent extends Event
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
