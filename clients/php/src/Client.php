<?php

namespace Jacanales\StatsD;

use Jacanales\StatsD\Metric\CounterMetric;

class Client
{
    private $connection;
    private $namespace;
    private $timers = [];

    public function __construct(Connection $connection, string $namespace = '')
    {
        $this->connection = $connection;
        $this->namespace = $namespace;
    }

    public function increment(string $key, float $sampleRate = 1.0, array $tags = []): void
    {
        $this->count($key, 1, $sampleRate, $tags);
    }

    public function decrement(string $key, float $sampleRate = 1.0, array $tags = []): void
    {
        $this->count($key, -1, $sampleRate, $tags);
    }

    public function count(string $key, int $value, float $sampleRate = 1.0, array $tags = []): void
    {
        $metric = new CounterMetric($key, $value, $tags);

        $this->send($metric, $sampleRate);
    }

    public function startTiming(string $key): void
    {
        $this->timers[$key] = \hrtime(true);
    }

    public function stopTiming(string $key, float $sampleRate = 1.0, array $tags = []): void
    {
        if (!isset($key, $this->timers)) {
            return;
        }

        $time = (\hrtime(true) - $this->timers[$key]);
        unset($this->timers[$key]);
    }

    public function timing(string $key, int $value, float $sampleRate = 1.0, array $tags = []): void
    {
        $metric = new CounterMetric($key, $value, $tags);

        $this->send($metric, $sampleRate);
    }

    public function send(Metric $metric, float $sampleRate): void
    {
        $this->connection->send($this->getMessage($metric, $sampleRate));
    }

    private function getMessage(Metric $metric, float $sampleRate): string
    {
        return $this->namespace . $metric->getMessage($sampleRate);
    }
}