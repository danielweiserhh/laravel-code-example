<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\IO\SocketIO;

class RabbitMQClient
{
    public function createConnection(): AMQPStreamConnection
    {
        $timeout = (float) config('services.ai.rabbitmq_timeout', 3.0);
        $heartbeat = (int) config('services.ai.rabbitmq_heartbeat', 30);
        $configuredReadWrite = (float) config('services.ai.rabbitmq_read_write_timeout', 3.0);
        $supportsKeepalive = function_exists('socket_import_stream');

        $minReadWrite = max($timeout, $heartbeat * 2.0);
        $readWriteTimeout = $configuredReadWrite < $minReadWrite ? $minReadWrite : $configuredReadWrite;

        $hosts = [[
            'host' => config('queue.connections.rabbitmq.host', 'rabbitmq'),
            'port' => config('queue.connections.rabbitmq.port', 5672),
            'user' => config('queue.connections.rabbitmq.login', 'kanban'),
            'password' => config('queue.connections.rabbitmq.password', 'kanban_password'),
            'vhost' => config('queue.connections.rabbitmq.vhost', '/'),
            'insist' => false,
            'login_method' => 'AMQPLAIN',
            'login_response' => null,
            'locale' => 'en_US',
            'connection_timeout' => $timeout,
            'read_write_timeout' => $readWriteTimeout,
            'context' => null,
            'keepalive' => $supportsKeepalive,
            'heartbeat' => $heartbeat,
            'channel_rpc_timeout' => $readWriteTimeout,
        ]];

        return AMQPStreamConnection::create_connection($hosts, [
            'insist' => false,
            'login_method' => 'AMQPLAIN',
            'login_response' => null,
            'locale' => 'en_US',
            'connection_timeout' => $timeout,
            'read_write_timeout' => $readWriteTimeout,
            'keepalive' => $supportsKeepalive,
            'heartbeat' => $heartbeat,
            'channel_rpc_timeout' => $readWriteTimeout,
            'io' => SocketIO::class,
        ]);
    }
}
