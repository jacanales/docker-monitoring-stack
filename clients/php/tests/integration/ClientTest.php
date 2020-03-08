<?php

namespace integration;

use Domnikl\Statsd\Connection;
use Jacanales\StatsD\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private const NAMESPACE = 'jacanales.statsd.';

    private $client;
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new class implements Connection {
            public $messages = [];

            public function send(string $message): void
            {
                $this->messages[] = $message;
            }

            public function sendMessages(array $messages): void
            {
                $this->messages = array_merge($this->messages, $messages);
            }

            public function close(): void
            {
                $this->messages = [];
            }
        };

        $this->client = new Client($this->connection, self::NAMESPACE);
    }

    protected function tearDown(): void
    {
        unset(
            $this->connection,
            $this->client,
        );

        parent::tearDown();
    }

    public function test_send_increment_to_connection(): void {
        $this->client->increment('test_connection');
        var_dump($this->connection->messages);
        $this->assertContains(self::NAMESPACE . 'test_connection:1|c', $this->connection->messages);
    }

}
