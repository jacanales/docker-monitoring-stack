<?php

declare(strict_types=1);

namespace Jacanales\StatsD\Metric;

use Jacanales\StatsD\Metric;

abstract class AbstractMetric implements Metric
{
    /** @var string */
    protected $key;

    /** @var mixed */
    protected $value;

    /** @var string[] */
    protected $tags;

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    abstract public function getType(): string;

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getMessage(float $sampleRate = 1.0): string
    {
        $message = $this->getKey() . ':' . $this->getValue() . '|' . $this->getType();

        $message = $this->addSampleRatio($message, $sampleRate);
        $message = $this->addTags($message);

        return $message;
    }

    /**
     * @param string $message
     * @param float $sampleRate
     * @return string
     */
    private function addSampleRatio(string $message, float $sampleRate): string
    {
        if ($sampleRate < 1.0) {
            $message .= '|@' . $sampleRate;
        }

        return $message;
    }

    private function addTags(string $message): string
    {
        if (empty($this->getTags())) {
            return $message;
        }

        $tagValues = [];
        foreach ($this->getTags() as $key => $value) {
            $tagValues[] = ($key . ':' . $value);
        }

        return $message . '|#' . implode(',', $tagValues);
    }
}