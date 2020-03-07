<?php


namespace Jacanales\StatsD\Metric;


class TimingMetric extends AbstractMetric
{
    public const TYPE = 'ms';

    public function __construct(string $key, int $value, array $tags = [])
    {
        $this->key = $key;
        $this->value = $value;
        $this->tags = $tags;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}