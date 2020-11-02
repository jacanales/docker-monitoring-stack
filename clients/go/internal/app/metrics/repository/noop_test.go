package repository_test

import (
	"testing"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics/repository"

	"github.com/golang/mock/gomock"
	"github.com/stretchr/testify/assert"
)

func TestNoOpWriter_Send(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	r := repository.NewNoOpWriter()

	err := r.Send(metrics.NewMockCountMetric(ctrl))

	assert.NoError(t, err)
}
