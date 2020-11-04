<?php

namespace RPaginate\Factory\Consumer;

use RPaginate\Factory\AMQPConnection;
use RPaginate\Factory\AMQPMessage;

class ConsumerManagement extends AMQPConnection
{
    public function amqpConnection(): AMQPMessage
    {
        $connection = $this->getConnection();
        
        return new Consumer($connection);
    }
}