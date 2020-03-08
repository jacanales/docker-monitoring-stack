<?php


namespace Jacanales\StatsD\Metric;


class SetMetric extends AbstractMetric
{
    public const TYPE = 's';

    public function __construct(string $key, string $value, array $tags = [])
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