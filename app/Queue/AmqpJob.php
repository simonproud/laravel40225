<?php

namespace App\Queue;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Contracts\Queue\Job as JobContract;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpJob extends Job implements JobContract
{
    protected AMQPMessage $amqpMessage;
    protected AMQPChannel $channel;
    protected AMQPStreamConnection $connection;
    protected $queue;
    protected $queueName;

    public function __construct($container, AmqpQueue $queue, AMQPMessage $message, $connection, $channel, $queueName)
    {
        $this->container = $container;
        $this->amqpMessage = $message;
        $this->connection = $connection;
        $this->channel = $channel;
        $this->queue = $queueName;
        $this->queueName = $queue;


        $this->amqpMessage->set('application_headers', [

        ]);
    }

    public function getRawBody()
    {
        return $this->amqpMessage->body;
    }

    public function fire()
    {
        if (method_exists($this, 'resolveAndFire')) {
            $this->resolveAndFire(json_decode($this->getRawBody(), true));
            return;
        }

        parent::fire();
    }

    public function delete()
    {
        parent::delete();
        $this->channel->basic_ack($this->amqpMessage->delivery_info['delivery_tag']);
    }

    public function release($delay = 0)
    {
        parent::release($delay);
        // Можно сделать requeue или перекинуть в retry-очередь
        $this->channel->basic_nack($this->amqpMessage->delivery_info['delivery_tag']);
    }

    public function getJobId()
    {
        return $this->amqpMessage->delivery_info['delivery_tag'];
    }

    public function attempts()
    {
        $headers = $this->amqpMessage->delivery_info['delivery_tag'];
        return $headers['x-attempts'] ?? 1;
    }
}
