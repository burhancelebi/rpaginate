<?php

namespace RPaginate\Commands;

use Illuminate\Console\Command;
use RPaginate\Builder\RPaginatorBuilder;
use RPaginate\Factory\Consumer\ConsumerManagement;
use PhpAmqpLib\Message\AMQPMessage;
use Predis\Client as Redis;
use Predis\Connection\ConnectionException;
use RPaginate\Exceptions\RedisConnection;

class Consumer extends Command
{
    public $redis;
    public static $key;
    private $connection;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbit:consumer {--queue=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data from RabbitMQ to add Redis';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $queue = $this->option('queue');
        
        if ( is_null($queue) )
        {
            return $this->error('Invalid queue');
        }

        self::$key = $queue;

        $this->connection = (new ConsumerManagement())->amqpConnection();
        $this->connection->queue($queue);
        $this->connection->exchange('router');
        $data_count = $this->connection->queueDeclare();
        $this->connection->exchangeDeclare();
        $this->connection->queueBind();

        $this->connection->basicConsume(['callback' => 'RPaginate\Commands\Consumer::processMessage']);

        while ($this->connection->channel()->is_consuming()) {

            try {
                printf("\n %d \n", $data_count[1]);
                if ( $data_count[1]-- == 0 ) {
                    $this->connection->close();
                    $this->info("Data saved to Redis");
                    $this->line("RabbitMQ is Closed");
                    return;
                }

                $this->connection->channel()->wait();
            } catch (\Exception $e) {
                //
            }
        }

        return 0;
    }

    public static function processMessage(AMQPMessage $message)
    {
        try {

            $messageBody = json_decode($message->body, 1);

            (new Redis())->rpush(self::$key, $messageBody['value']);

            $message->ack();

            if ($messageBody === 'quit') {
                $message->getChannel()->basic_cancel($message->getConsumerTag());
            }

            return (new self);
        } catch (ConnectionException $e) {
            throw new RedisConnection('Multiple ports detected');
        }
    }
}
