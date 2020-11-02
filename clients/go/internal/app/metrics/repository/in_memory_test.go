package repository_test

import (
	"testing"

	"github.com/golang/mock/gomock"
	"github.com/stretchr/testify/assert"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics/repository"
)

func TestInMemory(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	s := map[string]metrics.Metric{}

	m := repository.NewInMemoryWriter(s)

	err := m.Send(metrics.NewMockCountMetric(ctrl))

	assert.NoError(t, err)
	assert.Len(t, s, 1)
}
