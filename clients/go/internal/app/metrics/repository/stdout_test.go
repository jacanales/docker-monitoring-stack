package repository_test

import (
	"fmt"
	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics/repository"
	"testing"

	"github.com/stretchr/testify/assert"
)

type stdoutTestMetric struct {
}

func (m stdoutTestMetric) Name() string {
	return "stdout_test_metric"
}

func (m stdoutTestMetric) Tags() map[string]string {
	return map[string]string{"stdout_tag": "stdout_value"}
}

func (m stdoutTestMetric) Type() metrics.MetricType {
	return metrics.Increment
}

func TestInMemoryWriter_Send(t *testing.T) {
	m := stdoutTestMetric{}

	wrt := &repository.InMemoryWriter{}

	err := wrt.Send(m)

	assert.NoError(t, err)
	assert.Len(t, wrt.Metrics, 1)
	assert.Contains(t, wrt.Metrics[0], m.Name())
	assert.Contains(t, wrt.Metrics[0], fmt.Sprintf("Type:\t%v", m.Type()))
	assert.Contains(t, wrt.Metrics[0], fmt.Sprintf("Tags:\t%v", []string{"stdout_tag:stdout_value"}))
}
