<?php

namespace Jacanales\StatsD\Metric;

use PHPUnit\Framework\TestCase;

class CounterMetricTest extends TestCase
{
    private const KEY = 'counter_metric_test';

    private $metric = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metric = new CounterMetric(self::KEY, 1, ['tag1' => 'value1', 'tag2' => 'value2']);
    }

    public function testGetType(): void
    {
        $this->assertEquals(CounterMetric::TYPE, $this->metric->getType());
    }

    public function testGetMessageWithoutSampleRate(): void
    {
        $this->assertEquals('counter_metric_test:1|c|#tag1:value1,tag2:value2', $this->metric->getMessage());
    }

    public function testGetMessageWithSampleRate(): void
    {
        $this->assertEquals('counter_metric_test:1|c|@0.8|#tag1:value1,tag2:value2', $this->metric->getMessage(0.8));
    }
}
