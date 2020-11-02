package metrics_test

import (
	"testing"

	"github.com/golang/mock/gomock"
	"github.com/stretchr/testify/assert"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
)

func TestBuildMetricName(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	m := metrics.NewMockMetric(ctrl)
	m.EXPECT().Name().Return("test_metric")

	str := metrics.BuildMetricName(m)

	assert.Contains(t, str, "test_metric")
}
