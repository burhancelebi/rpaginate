<?php

namespace RPaginate\Factory\Publisher;

use RPaginate\Factory\AMQPConnection;
use RPaginate\Factory\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use RPaginate\Traits\RabbitMQ;

class Publisher implements AMQPMessage
{
    use RabbitMQ;
    
    private $connection;
    private $channel;
    private $exchange;
    private string $queue;

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->connection = $connection;

        $this->channel = $this->connection->channel();
    }

    public function channel()
    {
        return $this->channel;
    }

    public function queueDeclare(array $queue_declare = [])
    {
        return $this->channel->queue_declare($this->queue, 
                                            $queue_declare['passive'] ?? false, 
                                            $queue_declare['durable'] ?? true, 
                                            $queue_declare['exclusive'] ?? false, 
                                            $queue_declare['auto_delete'] ?? false
                                        );
    }

    public function exchangeDeclare(array $exchange_declare = [])
    {
        return $this->channel->exchange_declare($this->exchange,
                                            $exchange_declare['exchange_type'] ?? 'direct', 
                                            $exchange_declare['passive'] ?? false, 
                                            $exchange_declare['durable'] ?? true, 
                                            $exchange_declare['auto_delete'] ?? false
                                        );
    }

    public function queueBind()
    {
        $this->channel->queue_bind($this->queue, $this->exchange);
    }
    
    /**
     * Message body
     *
     * @return void
     */
    public function messageBody($closure, $function)
    {
        $function->call($closure);
    }

    public function close()
    {
        $this->connection->close();
        $this->channel->close();
    }
}