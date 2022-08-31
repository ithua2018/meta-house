<?php

namespace App\Events;

class HouseCollecetionEvent extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $house;
    public $id;
    public function __construct(House $house, ?int $id=null, $opt='')
    {
       $this->house = $house;
       $this->id = $id;
       $this->opt = $opt;
    }
}
