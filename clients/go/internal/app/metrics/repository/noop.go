package repository

import (
	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
)

type NoOpWriter struct {
}

func NewNoOpWriter() metrics.Writer {
	return NoOpWriter{}
}

func (n NoOpWriter) Send(metric metrics.Metric) error {
	return nil
}
