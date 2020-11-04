<?php

namespace RPaginate\Factory\Publisher;

use RPaginate\Factory\AMQPConnection;
use RPaginate\Factory\AMQPMessage;

class PublisherManagement extends AMQPConnection
{
    public function amqpConnection(): AMQPMessage
    {
        $connection = $this->getConnection();
        
        return new Publisher($connection);
    }
}