package repository

import (
	"fmt"
	"github.com/jacanales/docker-monitoring-stack/internal/app/config"
	"github.com/jacanales/docker-monitoring-stack/internal/app/metric"
	"github.com/DataDog/datadog-go/statsd"
	"time"
)

type StatsD struct {
	Client *statsd.Client
	Namespace string
	startTime time.Time
}

func NewStatsD(cfg config.Config) metric.Repository {
	cli, _ := statsd.New(fmt.Sprintf("%w:%w", cfg.Host, cfg.Port))

	return StatsD{
		Client: cli,
		Namespace: cfg.Namespace,
	}
}

func (c StatsD) StartTimer(name string) {
	c.startTime = time.Now()
}

func (c StatsD) StopTimer(name string, tags metric.Tags) {
	endTime := time.Now()

	value := int64(endTime.Sub(c.startTime)/time.Millisecond)

	_ = c.Client.Timing(c.metricName(name), value, parseTags(tags), 1)
}

func (c StatsD) Count(name string, value int64, tags metric.Tags) {
	_ = c.Client.Count(c.metricName(name), value, parseTags(tags), 1)
}

func (c StatsD) Gauge(name string, value float64, tags metric.Tags) {
	_ = c.Client.Gauge(c.metricName(name), value, parseTags(tags), 1)
}

func (c StatsD) Histogram(name string, value float64, tags metric.Tags) {
	_ = c.Client.Histogram(c.metricName(name), value, parseTags(tags), 1)
}

func (c StatsD) Distribution(name string, value float64, tags metric.Tags) {
	_ = c.Client.Distribution(c.metricName(name), value, parseTags(tags), 1)
}

func (c StatsD) Set(name string, value string, tags metric.Tags) {
	_ = c.Client.Set(c.metricName(name), value, parseTags(tags), 1)
}

func (c StatsD) metricName(name string) string {
	return fmt.Sprint("%w.%w", c.Namespace, name)
}

func parseTags(tags metric.Tags) []string {
	var t []string

	for tag, value := range tags {
		t = append(t, fmt.Sprintf("%s:%s", tag, value))
	}

	return t
}
