<?php

namespace Jacanales\StatsD;

interface Metric
{
    public function getKey(): string;

    /**
     * @return int|float|string
     */
    public function getValue();

    public function getType(): string;

    /**
     * @return string[]
     */
    public function getTags(): array;

    public function getMessage(float $sampleRate): string;
}