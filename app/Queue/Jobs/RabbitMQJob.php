<?php

namespace App\Queue\Jobs;

use App\Jobs\TestQueue;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseJob;
class RabbitMQJob extends BaseJob
{

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        $payload = $this->payload();

        $class = TestQueue::class;
        $method = 'handle';
        console_debug($payload['data']);
        ($this->instance = $this->resolve($class))->{$method}($this, $payload);

        $this->delete();
    }

    /**
     * Get the decoded body of the job.
     *
     * @return array
     */
    public function payload()
    {
        return [
            'job'  => TestQueue::class . '@handle',
            'data' => json_decode($this->getRawBody(), true)
        ];
    }
}
