<?php

namespace App\Queue\Connectors;

use Illuminate\Queue\Connectors\ConnectorInterface;
use App\Queue\AmqpQueue;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class AmqpConnector implements ConnectorInterface
{
    public function connect(array $config)
    {
        $connection = new AMQPStreamConnection(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config['vhost'] ?? '/'
        );

        $channel = $connection->channel();

        return new AmqpQueue(
            $connection,
            $channel,
            $config
        );
    }
}
