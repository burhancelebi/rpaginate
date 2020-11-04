<?php

namespace RPaginate\Factory;

use Closure;

interface AMQPMessage
{
    public function channel();
    
    public function queueDeclare(bool $passive, bool $durable, string $exclusive, bool $auto_delete);

    public function queueBind();
    
    public function exchangeDeclare(int $exchange_type, bool $passive, bool $durable, bool $auto_delete);

    // public function messageBody($closure, $function);
}