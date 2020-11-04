<?php declare(strict_types=1);

namespace RPaginate\Builder;

use RPaginate\Builder\RPaginatorBuilder;
use Predis\Client as Redis;
use PhpAmqpLib\Message\AMQPMessage;
use RPaginate\Factory\Publisher\PublisherManagement;

class RPaginator
{
    /**
     * Set how many data add to redis
     * 
     * @var $limit
     */
    protected $limit;

    /**
     * it will determine how many data has in redis key
     * 
     * @var $total
     */
    protected int $total;

    /**
     * set the data key to call
     * 
     * @var $key
     */
    protected string $key;

    /**
     * Data count in redis
     * 
     * @var $count
     */
    protected int $count;

    /**
     * Determine Model class
     * 
     * @var model
     */
    protected object $model;

    /**
     * Data from the Model
     * 
     * @var $data
     */
    protected $data;

    /**
     * Connect to Client\Redis
     * 
     * @var $redis
     */
    protected object $redis;

    protected $connection;
    
    public function __construct(RPaginatorBuilder $builder)
    {
        $this->redis = new Redis();

        $this->limit = $builder->limit;
        $this->model = $builder->model;
        $this->data = $builder->data;
        $this->key = $builder->key;
        $this->pipe();
    }

    /**
     * Get data from builder and you can change data for RQuery function
     *
     * @param  mixed $data
     * @return void
     */
    public function setData($data = null)
    {
        if ( !is_null($data) ) {

            $this->data = $data;
        }

        return $this;
    }

    /**
     * Add data to Redis using pipeline
     *
     * @return void
     */
    public function pipe()
    {
        $this->saveTotal();
        
        $this->connection = (new PublisherManagement())->amqpConnection();
        $this->connection->queue($this->key);
        $this->connection->exchange('router');
        $closure = (function(){
            $this->connection->queueDeclare();
            $this->connection->exchangeDeclare();
            $this->connection->queueBind();
            
            $i = 1;
            foreach ($this->data as $key => $value) {
                printf("\n %d", $i++);
                $messageBody = json_encode(['key' => $this->key, 'value' => serialize($value)]);
                $message = new AMQPMessage($messageBody, [
                    'content_type' => 'application/json', 
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]);

                $this->connection->channel()->basic_publish($message, 'router');
            }
        });

        $this->connection->messageBody($this, $closure);
    }

    /**
     *
     * @return $this
     */
    public function count()
    {
        $this->count = count($this->data);

        return $this->count;
    }
    
    /**
     * Save total data
     *
     * @return void
     */
    private function saveTotal(): void
    {
        $this->total = $this->count();
        
        $this->redis->set($this->key . '_total', $this->total);
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}