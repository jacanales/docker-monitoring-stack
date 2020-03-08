<?php

namespace tests\unit\Jacanales\StatsD;

use Domnikl\Statsd\Connection;
use Jacanales\StatsD\Client;
use Jacanales\StatsD\Metric\CounterMetric;
use Jacanales\StatsD\Metric\DistributionMetric;
use Jacanales\StatsD\Metric\GaugeMetric;
use Jacanales\StatsD\Metric\HistogramMetric;
use Jacanales\StatsD\Metric\SetMetric;
use Jacanales\StatsD\Metric\TimingMetric;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

final class ClientTest extends TestCase
{
    private const METRIC_NAME = 'test_metric';
    private const SAMPLE_RATE = 0.8;
    private const TAGS = ['tag1' => 'value1', 'tag2' => 'value2'];

    private $client;

    /** @var ObjectProphecy|Connection  */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->prophesize(Connection::class);
        $this->client = new Client($this->connection->reveal());
    }

    protected function tearDown(): void
    {
        unset(
            $this->client,
            $this->connection,
        );

        parent::tearDown();
    }

    public function test_increment(): void
    {
        $this->connection->send(self::METRIC_NAME . ':1|' . CounterMetric::TYPE)->shouldBeCalled();
        $this->client->increment(self::METRIC_NAME);
    }

    public function test_increment_with_sample_rate(): void
    {
        $this->connection->send(self::METRIC_NAME . ':1|' . CounterMetric::TYPE . '|@' . self::SAMPLE_RATE)->shouldBeCalled();
        $this->client->increment(self::METRIC_NAME, [], self::SAMPLE_RATE);
    }

    public function test_increment_with_tags(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':1|' . CounterMetric::TYPE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->increment(self::METRIC_NAME, self::TAGS);
    }

    public function test_increment_with_tags_and_sample_rate(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':1|' . CounterMetric::TYPE . '|@' . self::SAMPLE_RATE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->increment(self::METRIC_NAME, self::TAGS, self::SAMPLE_RATE);
    }

    public function test_decrement(): void
    {
        $this->connection->send(self::METRIC_NAME . ':-1|' . CounterMetric::TYPE)->shouldBeCalled();
        $this->client->decrement(self::METRIC_NAME);
    }

    public function test_derement_with_sample_rate(): void
    {
        $this->connection->send(self::METRIC_NAME . ':-1|' . CounterMetric::TYPE . '|@' . self::SAMPLE_RATE)->shouldBeCalled();
        $this->client->decrement(self::METRIC_NAME, [], self::SAMPLE_RATE);
    }

    public function test_decrement_with_tags(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':-1|' . CounterMetric::TYPE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->decrement(self::METRIC_NAME, self::TAGS);
    }

    public function test_decrement_with_tags_and_sample_rate(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':-1|' . CounterMetric::TYPE . '|@' . self::SAMPLE_RATE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->decrement(self::METRIC_NAME, self::TAGS, self::SAMPLE_RATE);
    }

    public function test_count(): void
    {
        $this->connection->send(self::METRIC_NAME . ':5|' . CounterMetric::TYPE)->shouldBeCalled();
        $this->client->count(self::METRIC_NAME, 5);
    }

    public function test_count_with_sample_rate(): void
    {
        $this->connection->send(self::METRIC_NAME . ':5|' . CounterMetric::TYPE . '|@' . self::SAMPLE_RATE)->shouldBeCalled();
        $this->client->count(self::METRIC_NAME, 5, [], self::SAMPLE_RATE);
    }

    public function test_count_with_tags(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':5|' . CounterMetric::TYPE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->count(self::METRIC_NAME, 5, self::TAGS);
    }

    public function test_count_with_tags_and_sample_rate(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':5|' . CounterMetric::TYPE . '|@' . self::SAMPLE_RATE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->count(self::METRIC_NAME, 5, self::TAGS, self::SAMPLE_RATE);
    }

    public function test_gauge(): void
    {
        $this->connection->send(self::METRIC_NAME . ':5.3|' . GaugeMetric::TYPE)->shouldBeCalled();
        $this->client->gauge(self::METRIC_NAME, 5.3);
    }

    public function test_gauge_with_sample_rate(): void
    {
        $this->connection->send(self::METRIC_NAME . ':5|' . GaugeMetric::TYPE . '|@' . self::SAMPLE_RATE)->shouldBeCalled();
        $this->client->gauge(self::METRIC_NAME, 5, [], self::SAMPLE_RATE);
    }

    public function test_gauge_with_tags(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':5|' . GaugeMetric::TYPE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->gauge(self::METRIC_NAME, 5, self::TAGS);
    }

    public function test_gauge_with_tags_and_sample_rate(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':5|' . GaugeMetric::TYPE . '|@' . self::SAMPLE_RATE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->gauge(self::METRIC_NAME, 5, self::TAGS, self::SAMPLE_RATE);
    }

    public function test_set(): void
    {
        $this->connection->send(self::METRIC_NAME . ':metric_value|' . SetMetric::TYPE)->shouldBeCalled();
        $this->client->set(self::METRIC_NAME, 'metric_value');
    }

    public function test_set_with_sample_rate(): void
    {
        $this->connection->send(self::METRIC_NAME . ':metric_value|' . SetMetric::TYPE . '|@' . self::SAMPLE_RATE)->shouldBeCalled();
        $this->client->set(self::METRIC_NAME, 'metric_value', [], self::SAMPLE_RATE);
    }

    public function test_set_with_tags(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':metric_value|' . SetMetric::TYPE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->set(self::METRIC_NAME, 'metric_value', self::TAGS);
    }

    public function test_set_with_tags_and_sample_rate(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':metric_value|' . SetMetric::TYPE . '|@' . self::SAMPLE_RATE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->set(self::METRIC_NAME, 'metric_value', self::TAGS, self::SAMPLE_RATE);
    }

    public function test_distribution(): void
    {
        $this->connection->send(self::METRIC_NAME . ':5|' . DistributionMetric::TYPE)->shouldBeCalled();
        $this->client->distribution(self::METRIC_NAME, 5);
    }

    public function test_distribution_with_sample_rate(): void
    {
        $this->connection->send(self::METRIC_NAME . ':5|' . DistributionMetric::TYPE . '|@' . self::SAMPLE_RATE)->shouldBeCalled();
        $this->client->distribution(self::METRIC_NAME, 5, [], self::SAMPLE_RATE);
    }

    public function test_distribution_with_tags(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':5|' . DistributionMetric::TYPE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->distribution(self::METRIC_NAME, 5, self::TAGS);
    }

    public function test_distribution_with_tags_and_sample_rate(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':5|' . DistributionMetric::TYPE . '|@' . self::SAMPLE_RATE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->distribution(self::METRIC_NAME, 5, self::TAGS, self::SAMPLE_RATE);
    }

    public function test_histogram(): void
    {
        $this->connection->send(self::METRIC_NAME . ':5|' . HistogramMetric::TYPE)->shouldBeCalled();
        $this->client->histogram(self::METRIC_NAME, 5);
    }

    public function test_histogram_with_sample_rate(): void
    {
        $this->connection->send(self::METRIC_NAME . ':5|' . HistogramMetric::TYPE . '|@' . self::SAMPLE_RATE)->shouldBeCalled();
        $this->client->histogram(self::METRIC_NAME, 5, [], self::SAMPLE_RATE);
    }

    public function test_histogram_with_tags(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':5|' . HistogramMetric::TYPE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->histogram(self::METRIC_NAME, 5, self::TAGS);
    }

    public function test_histogram_with_tags_and_sample_rate(): void
    {
        $tags = [];
        foreach (self::TAGS as $tag => $value) {
            $tags[] = sprintf('%s:%s', $tag, $value);
        }

        $this->connection->send(self::METRIC_NAME . ':5|' . HistogramMetric::TYPE . '|@' . self::SAMPLE_RATE . '|#' . implode(',', $tags))->shouldBeCalled();
        $this->client->histogram(self::METRIC_NAME, 5, self::TAGS, self::SAMPLE_RATE);
    }

    public function test_timing(): void
    {
        $this->client->startTimer(self::METRIC_NAME);

        $this->client->stopTimer(self::METRIC_NAME);

        $argument = new RegexToken('/^' . self::METRIC_NAME . ':\d+\|' . TimingMetric::TYPE . '$/');

        $this->connection->send($argument)->shouldBeCalled();
    }

    public function test_stop_timing_without_start(): void
    {
        $this->client->stopTimer(self::METRIC_NAME);

        $this->connection->send(Argument::any())->shouldNotBeCalled();
    }
}

class RegexToken implements Argument\Token\TokenInterface
{
    /**
     * @var string
     */
    private $_pattern;

    /**
     * Creates token for matching to regular expression.
     *
     * @param string $pattern Pattern.
     */
    public function __construct(string $pattern)
    {
        $this->_pattern = $pattern;
    }

    /**
     * Calculates token match score for provided argument.
     *
     * @param string $argument Argument.
     *
     * @return boolean|integer
     */
    public function scoreArgument($argument)
    {
        return preg_match($this->_pattern, $argument) ? 6 : false;
    }

    /**
     * Returns true if this token prevents check of other tokens (is last one).
     *
     * @return boolean|integer
     */
    public function isLast()
    {
        return false;
    }

    /**
     * Returns string representation for token.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('matches("%s")', $this->_pattern);
    }
}
