<?php

namespace App\Events;

class HouseCollecetionDeletedEvent extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $id;
    public function __construct(int $id)
    {
       $this->id = $id;
    }
}
