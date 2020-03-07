<?php


namespace Jacanales\StatsD\Metric;


class DistributionMetric extends AbstractMetric
{
    public const TYPE = 'd';

    public function __construct(string $key, float $value, array $tags = [])
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