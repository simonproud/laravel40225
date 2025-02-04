<?php

namespace App\Queue;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpQueue extends Queue implements QueueContract
{
    protected $connection;
    protected AMQPChannel $channel;
    protected $config;

    public function __construct($connection, $channel, array $config)
    {
        $this->connection = $connection;
        $this->channel = $channel;
        $this->config = $config;
    }

    public function size($queue = null)
    {
        // Получение количества сообщений в очереди (queue_declare)
        $queue = $this->getQueueName($queue);

        list($queue, $messageCount, $consumerCount) = $this->channel->queue_declare(
            $queue,
            true,
            false,
            false,
            false
        );
        return $messageCount;
    }

    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $message = new AMQPMessage($payload, [
            'content_type' => 'text/plain',
            'delivery_mode' => 2,
        ]);
        $this->channel->basic_publish(
            $message,
            $this->config['exchange'] ?? ''
        );

        return 0;
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        // Для реализации задержки с помощью TTL можно настроить отдельный exchange и очередь
        // или использовать плагины RabbitMQ. Ниже — упрощённая версия без реализации TTL.
        return $this->push($job, $data, $queue);
    }

    public function pop($queue = null)
    {
        $queue = $this->getQueueName($queue);

        $message = $this->channel->basic_get($queue);

        if (! $message) {
            return null;
        }

        return new AmqpJob(
            $this->container,
            $this,
            $message,
            $this->connection,
            $this->channel,
            $queue
        );
    }

    public function getQueueName($queue)
    {
        return $queue ?: ($this->config['queue'] ?? 'default');
    }
}
