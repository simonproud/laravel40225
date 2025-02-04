<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpAmqpLib\Connection\AMQPStreamConnection;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', '127.0.0.1'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest'),
            env('RABBITMQ_VHOST', '/')
        );
        $channel = $connection->channel();

        $channel->exchange_declare(
            'balance_exchange',
            'direct',
            false,
            true,
            false
        );

        $channel->queue_declare(
            'balance_queue',
            false,
            true,
            false,
            false
        );

        $channel->queue_bind(
            'balance_queue',
            'balance_exchange'
        );

        $channel->close();
        $connection->close();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', '127.0.0.1'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest'),
            env('RABBITMQ_VHOST', '/')
        );
        $channel = $connection->channel();
        $channel->queue_unbind('balance_queue', 'balance_exchange');
        $channel->queue_delete('balance_queue');
        $channel->exchange_delete('balance_exchange');
        $channel->close();
        $connection->close();
    }
};
