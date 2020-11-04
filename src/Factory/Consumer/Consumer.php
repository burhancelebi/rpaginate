<?php

namespace RPaginate\Factory\Consumer;

use RPaginate\Factory\AMQPConnection;
use RPaginate\Factory\AMQPMessage as AMQPMessageInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use RPaginate\Traits\RabbitMQ;

class Consumer implements AMQPMessageInterface
{
    use RabbitMQ;
    
    private $connection;
    private $channel;
    private $exchange;
    private $consumerTag = 'consumer';
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

    public function queueDeclare($passive = false, $durable = true, $exclusive = false, $auto_delete = false)
    {
        return $this->channel->queue_declare($this->queue, 
                                            $passive,
                                            $durable,
                                            $exclusive, 
                                            $auto_delete
                                        );
    }

    public function exchangeDeclare($exchange_type = 'direct', $passive = false, $durable = true, $auto_delete = false)
    {
        return $this->channel->exchange_declare($this->exchange,
                                            $exchange_type, 
                                            $passive = false, 
                                            $durable = true, 
                                            $auto_delete = false
                                        );
    }

    public function queueBind()
    {
        $this->channel->queue_bind($this->queue, $this->exchange);
    }

    public function setConsumerTag(string $consumerTag)
    {
        $this->consumerTag = $consumerTag;

        return $this;
    }

    public function basicConsume(array $consume = [])
    {
        $this->channel->basic_consume($this->queue,
                                    $this->consumerTag,
                                    $no_local = $consume['no_local'] ?? false,
                                    $no_ack = $consume['no_ack'] ?? false, 
                                    $exclusive = $consume['exclusive'] ?? false,
                                    $nowait = $consume['nowait'] ?? false,
                                    $consume['callback'],
                                );
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}