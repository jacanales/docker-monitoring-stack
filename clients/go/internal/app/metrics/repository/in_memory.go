package repository

import (
	"time"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
)

type InMemory struct {
	metrics map[string]metrics.Metric
}

func NewInMemoryWriter(storage map[string]metrics.Metric) metrics.Writer {
	return InMemory{metrics: storage}
}

func (i InMemory) Send(metric metrics.Metric) error {
	i.metrics[time.Now().Format(time.RFC3339Nano)] = metric

	return nil
}
