//go:generate mockgen -source=$GOFILE -destination=mock_$GOFILE -package=$GOPACKAGE -self_package=$GOPACKAGE

package repository

import (
	"fmt"
	"sync"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"

	"github.com/datadog/datadog-go/statsd"
)

var (
	once   sync.Once
	client ClientInterface
)

type ClientInterface interface {
	statsd.ClientInterface
}

type StatsDWriter struct {
	Client ClientInterface
}

func NewStatsDWriter(client ClientInterface) metrics.Writer {
	return StatsDWriter{Client: client}
}

func NewDataDogClient(cfg metrics.Config) (ClientInterface, error) {
	var err error
	once.Do(func() {
		client, err = statsd.New(cfg.Addr, statsd.WithoutTelemetry())
	})

	return client, err
}

func (w StatsDWriter) Send(m metrics.Metric) error {
	switch m.Type() {
	case metrics.Gauge:
		return w.sendGauge(m.(metrics.GaugeMetric))
	case metrics.Count:
		return w.sendCount(m.(metrics.CountMetric))
	case metrics.Increment:
		return w.Client.Incr(metrics.BuildMetricName(m), buildTags(m.Tags()), 1)
	case metrics.Decrement:
		return w.Client.Decr(metrics.BuildMetricName(m), buildTags(m.Tags()), 1)
	case metrics.Histogram:
		return w.sendHistogram(m.(metrics.HistogramMetric))
	case metrics.Timing:
		return w.sendTiming(m.(metrics.TimingMetric))
	default:
		return fmt.Errorf("not supported metric type: %v", m.Type())
	}
}

func (w StatsDWriter) sendGauge(m metrics.GaugeMetric) error {
	return w.Client.Gauge(metrics.BuildMetricName(m), m.Value(), buildTags(m.Tags()), 1)
}

func (w StatsDWriter) sendHistogram(m metrics.HistogramMetric) error {
	return w.Client.Histogram(metrics.BuildMetricName(m), m.Value(), buildTags(m.Tags()), 1)
}

func (w StatsDWriter) sendCount(m metrics.CountMetric) error {
	return w.Client.Count(metrics.BuildMetricName(m), m.Value(), buildTags(m.Tags()), 1)
}

func (w StatsDWriter) sendTiming(m metrics.TimingMetric) error {
	return w.Client.Timing(metrics.BuildMetricName(m), m.Value(), buildTags(m.Tags()), 1)
}

func buildTags(tags map[string]string) []string {
	parsed := make([]string, 0, len(tags))

	for tag, value := range tags {
		parsed = append(parsed, tag+":"+value)
	}

	return parsed
}
