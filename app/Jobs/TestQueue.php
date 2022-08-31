<?php

namespace App\Jobs;

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestQueue implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle() {
        // 插入数据
       // Post::create($this->data);
        Log::info(json_encode($this->data));
        console_debug(json_encode($this->data));
       // var_dump($this->data);
       // return true;
    }
}

