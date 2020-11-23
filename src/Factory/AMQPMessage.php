<?php

namespace RPaginate\Factory;

use Closure;

interface AMQPMessage
{
    public function channel();
    
    public function queueDeclare(array $queue_declare = []);

    public function queueBind();
    
    public function exchangeDeclare(array $exchange_declare = []);

    // public function messageBody($closure, $function);
}