<?php

namespace tests\unit\Jacanales\StatsD\Metric;

use Jacanales\StatsD\Metric\CounterMetric;
use PHPUnit\Framework\TestCase;

class CounterMetricTest extends TestCase
{
    private const KEY = 'counter_metric_test';
    private const DEFAULT_VALUE = 1;

    private $metric;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metric = new CounterMetric(self::KEY, self::DEFAULT_VALUE);
    }

    protected function tearDown(): void
    {
        $this->metric = null;

        parent::tearDown();
    }

    public function testGetType(): void
    {
        $this->assertEquals(CounterMetric::TYPE, $this->metric->getType());
    }

    public function testGetMessage(): void
    {
        $expectedMessage = sprintf('%s:%d|%s', self::KEY, self::DEFAULT_VALUE, CounterMetric::TYPE);

        $this->assertEquals($expectedMessage, $this->metric->getMessage());
    }

    public function testGetMessageWithTags(): void
    {
        $value = 5;
        $this->metric = new CounterMetric(self::KEY, $value, ['tag1' => 'value1', 'tag2' => 'value2']);

        $expectedMessage = sprintf('%s:%d|%s|#tag1:value1,tag2:value2', self::KEY, $value, CounterMetric::TYPE);

        $this->assertEquals($expectedMessage, $this->metric->getMessage());
    }

    public function testGetMessageWithSampleRate(): void
    {
        $rate = 0.8;

        $expectedMessage = sprintf('%s:%d|%s|@%s', self::KEY, self::DEFAULT_VALUE, CounterMetric::TYPE, $rate);

        $this->assertEquals($expectedMessage, $this->metric->getMessage($rate));
    }

    public function testGetMessageWithTagsAndSampleRate(): void
    {
        $rate = 0.8;
        $this->metric = new CounterMetric(self::KEY, self::DEFAULT_VALUE, ['tag1' => 'value1', 'tag2' => 'value2']);

        $expectedMessage = sprintf('%s:%d|%s|@%s|#tag1:value1,tag2:value2', self::KEY, self::DEFAULT_VALUE, CounterMetric::TYPE, $rate);
        $this->assertEquals($expectedMessage, $this->metric->getMessage($rate));
    }
}
