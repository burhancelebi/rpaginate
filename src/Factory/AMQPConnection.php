<?php

namespace RPaginate\Factory;

use PhpAmqpLib\Connection\AMQPStreamConnection;

abstract class AMQPConnection
{
    private $config;
    
    abstract protected function amqpConnection(): AMQPMessage;

    public function __construct()
    {
        $this->config = config('queue.connections.rabbitmq.hosts');
    }

    protected function getConnection(): AMQPStreamConnection
    {
        $connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'],
        );

        return $connection;
    }
}