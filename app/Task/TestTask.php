<?php

namespace App\Task;

use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

class TestTask  extends  Task
{
    private $data;
    private $result;
    public function __construct($data)
    {
        $this->data = $data;
    }
    // The logic of task handling, run in task process, CAN NOT deliver task
    public function handle()
    {
        Log::info(__CLASS__ . ':handle start', $this->data);
        console_debug($this->data);
        sleep(2);// Simulate the slow codes
        // The exceptions thrown here will be caught by the upper layer and recorded in the Swoole log. Developers need to try/catch manually.
        $this->result = 'the result of ' . json_encode($this->data);
    }
    // Optional, finish event, the logic of after task handling, run in worker process, CAN deliver task
    public function finish()
    {
        Log::info(__CLASS__ . ':finish start', [$this->result]);
      //  Task::deliver(new TestTask2('task2 data')); // Deliver the other task
    }
}
