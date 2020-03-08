<?php

declare(strict_types=1);

namespace Jacanales\StatsD\Metric;

class CounterMetric extends AbstractMetric
{
    public const TYPE = 'c';

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