<?php

namespace Jacanales\StatsD;

use Domnikl\Statsd\Connection;
use Jacanales\StatsD\Metric\CounterMetric;
use Jacanales\StatsD\Metric\DistributionMetric;
use Jacanales\StatsD\Metric\GaugeMetric;
use Jacanales\StatsD\Metric\HistogramMetric;
use Jacanales\StatsD\Metric\SetMetric;
use Jacanales\StatsD\Metric\TimingMetric;

final class Client
{
    private $connection;
    private $namespace;
    private $timers = [];

    public function __construct(Connection $connection, string $namespace = '')
    {
        $this->connection = $connection;
        $this->namespace = $namespace;
    }

    public function increment(string $key, array $tags = [], float $sampleRate = 1.0): void
    {
        $this->count($key, 1, $tags, $sampleRate);
    }

    public function decrement(string $key, array $tags = [], float $sampleRate = 1.0): void
    {
        $this->count($key, -1, $tags, $sampleRate);
    }

    public function count(string $key, int $value, array $tags = [], float $sampleRate = 1.0): void
    {
        $metric = new CounterMetric($key, $value, $tags);

        $this->send($metric, $sampleRate);
    }

    public function distribution(string $key, float $value, array $tags = [], float $sampleRate = 1.0): void
    {
        $metric = new DistributionMetric($key, $value, $tags);

        $this->send($metric, $sampleRate);
    }

    public function gauge(string $key, float $value, array $tags = [], float $sampleRate = 1.0): void
    {
        $metric = new GaugeMetric($key, $value, $tags);

        $this->send($metric, $sampleRate);
    }

    public function set(string $key, string $value, array $tags = [], float $sampleRate = 1.0): void
    {
        $metric = new SetMetric($key, $value, $tags);

        $this->send($metric, $sampleRate);
    }

    public function histogram(string $key, float $value, array $tags = [], float $sampleRate = 1.0): void
    {
        $metric = new HistogramMetric($key, $value, $tags);

        $this->send($metric, $sampleRate);
    }

    public function startTimer(string $key): void
    {
        $this->timers[$key] = \hrtime(true);
    }

    public function stopTimer(string $key, array $tags = [], float $sampleRate = 1.0): void
    {
        if (!isset($this->timers[$key])) {
            return;
        }

        $time = (\hrtime(true) - $this->timers[$key]);
        unset($this->timers[$key]);

        $this->timing($key, $time, $tags, $sampleRate);
    }

    private function timing(string $key, int $value, array $tags = [], float $sampleRate = 1.0): void
    {
        $metric = new TimingMetric($key, $value, $tags);

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
