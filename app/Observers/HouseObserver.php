<?php

namespace App\Observers;
use Illuminate\Support\Facades\Log;

trait HouseObserver
{
   protected static function boot()
   {
      parent::boot();
      static::created(function($post) {
         Log::info('观察者一号', [$post]);
      });

       static::updated(function($post) {
           Log::info('观察者一号', [$post]);
       });

       static::deleted(function($post) {
           Log::info('观察者一号', [$post]);
       });
   }

}
