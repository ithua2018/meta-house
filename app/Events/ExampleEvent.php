<?php

namespace App\Events;

class ExampleEvent extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $test;
    public function __construct($a)
    {
      //  console_debug($a);
      $this->test = $a;
    }
}
