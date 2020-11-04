<?php

namespace RPaginate\Traits;

trait RabbitMQ
{
    public function queue($queue = null)
    {
        if ( is_null($queue) ) {
            
            throw new \Exception('Queue name is required');
        }
        
        $this->queue = $queue;
    }

    public function exchange($exchange = null)
    {
        if ( is_null($exchange) ) {
            
            throw new \Exception('Exchange name is required');
        }
        
        $this->exchange = $exchange;
    }
}