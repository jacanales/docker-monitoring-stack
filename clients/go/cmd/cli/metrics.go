package main

import (
"math/rand"
"time"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
)

const (
	maxResponseTime = 2000
	maxMemoryUsage  = 500
	maxCount        = 30
)

type GaugeTest struct{}

func (g GaugeTest) Name() string {
	return "pe_gauge_test"
}

func (g GaugeTest) Tags() map[string]string {
	return map[string]string{
		"project": "datadog_integration",
	}
}

func (g GaugeTest) Type() metrics.MetricType {
	return metrics.Gauge
}

func (g GaugeTest) Value() float64 {
	return float64(rand.Intn(maxMemoryUsage))
}

type HistogramTest struct{}

func (h HistogramTest) Name() string {
	return "pe_histogram_test"
}

func (h HistogramTest) Tags() map[string]string {
	return map[string]string{
		"project": "datadog_integration",
	}
}

func (h HistogramTest) Type() metrics.MetricType {
	return metrics.Histogram
}

func (h HistogramTest) Value() float64 {
	return float64(rand.Intn(maxCount))
}

type CountTest struct{}

func (c CountTest) Name() string {
	return "pe_count_test"
}

func (c CountTest) Tags() map[string]string {
	return map[string]string{
		"project": "datadog_integration",
	}
}

func (c CountTest) Type() metrics.MetricType {
	return metrics.Count
}

func (c CountTest) Value() int64 {
	return int64(rand.Intn(maxCount))
}

type IncrTest struct{}

func (i IncrTest) Name() string {
	return "pe_counter_test"
}

func (i IncrTest) Tags() map[string]string {
	return map[string]string{
		"project": "datadog_integration",
	}
}

func (i IncrTest) Type() metrics.MetricType {
	return metrics.Increment
}

type DecrTest struct{}

func (d DecrTest) Name() string {
	return "pe_counter_test"
}

func (d DecrTest) Tags() map[string]string {
	return map[string]string{
		"project": "datadog_integration",
	}
}

func (d DecrTest) Type() metrics.MetricType {
	return metrics.Decrement
}

type TimingTest struct{}

func (t TimingTest) Name() string {
	return "pe_timing_test"
}

func (t TimingTest) Tags() map[string]string {
	return map[string]string{
		"project": "datadog_integration",
	}
}

func (t TimingTest) Type() metrics.MetricType {
	return metrics.Timing
}

func (t TimingTest) Value() time.Duration {
	return time.Duration(rand.Intn(maxResponseTime)) * time.Millisecond
}
