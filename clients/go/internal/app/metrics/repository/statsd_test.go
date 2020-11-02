package repository_test

import (
	"net/http"
	"net/http/httptest"
	"testing"
	"time"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics/repository"

	"github.com/golang/mock/gomock"
	"github.com/stretchr/testify/assert"
)

const (
	metricName = "statsd_metric_name"
)

var metricTags = map[string]string{"statsd_tag_name": "statsd_tag_value"}

func TestNewDataDogClient(t *testing.T) {
	srv := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		_, _ = w.Write([]byte("ok"))
	}))

	cfg := metrics.Config{Addr: srv.Listener.Addr().String()}

	cli, err := repository.NewDataDogClient(cfg)

	t.Run("it connects to datadog client", func(t *testing.T) {
		assert.NoError(t, err)
		assert.NotNil(t, cli)
	})

	t.Run("it reuses connection", func(t *testing.T) {
		cli2, err := repository.NewDataDogClient(cfg)

		assert.NoError(t, err)
		assert.Equal(t, cli, cli2)
	})
}

func TestNewStatsDWriter(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	cli := repository.NewMockClientInterface(ctrl)
	writer := repository.NewStatsDWriter(cli)

	assert.Implements(t, (*metrics.Writer)(nil), writer)
	assert.IsType(t, repository.StatsDWriter{}, writer)
}

func TestStatsDWriter_Send_Gauge(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	val := 15.0

	cli := repository.NewMockClientInterface(ctrl)
	cli.EXPECT().Gauge("company.project."+metricName, val, []string{"statsd_tag_name:statsd_tag_value"}, float64(1)).Return(nil)

	writer := repository.NewStatsDWriter(cli)
	metric := havingAGaugeMetric(ctrl, val)

	err := writer.Send(metric)
	assert.NoError(t, err)
}

func TestStatsDWriter_Send_Count(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	val := int64(15)

	cli := repository.NewMockClientInterface(ctrl)
	cli.EXPECT().Count("company.project."+metricName, val, []string{"statsd_tag_name:statsd_tag_value"}, float64(1)).Return(nil)

	writer := repository.NewStatsDWriter(cli)
	metric := havingACountMetric(ctrl, val)

	err := writer.Send(metric)
	assert.NoError(t, err)
}

func TestStatsDWriter_Send_Increment(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	cli := repository.NewMockClientInterface(ctrl)
	cli.EXPECT().Incr("company.project."+metricName, []string{"statsd_tag_name:statsd_tag_value"}, float64(1)).Return(nil)

	writer := repository.NewStatsDWriter(cli)
	metric := havingAMetric(ctrl)
	metric.EXPECT().Type().Return(metrics.Increment)

	err := writer.Send(metric)
	assert.NoError(t, err)
}

func TestStatsDWriter_Send_Decrement(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	cli := repository.NewMockClientInterface(ctrl)
	cli.EXPECT().Decr("company.project."+metricName, []string{"statsd_tag_name:statsd_tag_value"}, float64(1)).Return(nil)

	writer := repository.NewStatsDWriter(cli)
	metric := havingAMetric(ctrl)
	metric.EXPECT().Type().Return(metrics.Decrement)

	err := writer.Send(metric)
	assert.NoError(t, err)
}

func TestStatsDWriter_Send_Histogram(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	val := 15.0

	cli := repository.NewMockClientInterface(ctrl)
	cli.EXPECT().Histogram("company.project."+metricName, val, []string{"statsd_tag_name:statsd_tag_value"}, float64(1)).Return(nil)

	writer := repository.NewStatsDWriter(cli)
	metric := havingAHistogramMetric(ctrl, val)

	err := writer.Send(metric)
	assert.NoError(t, err)
}

func TestStatsDWriter_Send_Timing(t *testing.T) {
	ctrl := gomock.NewController(t)
	defer ctrl.Finish()

	val := 4 * time.Second

	cli := repository.NewMockClientInterface(ctrl)
	cli.EXPECT().Timing("company.project."+metricName, val, []string{"statsd_tag_name:statsd_tag_value"}, float64(1)).Return(nil)

	writer := repository.NewStatsDWriter(cli)
	metric := havingATimingMetric(ctrl, val)

	err := writer.Send(metric)
	assert.NoError(t, err)
}

func havingAGaugeMetric(ctrl *gomock.Controller, val float64) *metrics.MockGaugeMetric {
	metric := metrics.NewMockGaugeMetric(ctrl)
	metric.EXPECT().Name().Return(metricName)
	metric.EXPECT().Tags().Return(metricTags)
	metric.EXPECT().Type().Return(metrics.Gauge)
	metric.EXPECT().Value().Return(val)

	return metric
}

func havingACountMetric(ctrl *gomock.Controller, val int64) *metrics.MockCountMetric {
	metric := metrics.NewMockCountMetric(ctrl)
	metric.EXPECT().Name().Return(metricName)
	metric.EXPECT().Tags().Return(metricTags)
	metric.EXPECT().Type().Return(metrics.Count)
	metric.EXPECT().Value().Return(val)

	return metric
}

func havingAMetric(ctrl *gomock.Controller) *metrics.MockMetric {
	metric := metrics.NewMockMetric(ctrl)
	metric.EXPECT().Name().Return(metricName)
	metric.EXPECT().Tags().Return(metricTags)

	return metric
}

func havingAHistogramMetric(ctrl *gomock.Controller, val float64) *metrics.MockHistogramMetric {
	metric := metrics.NewMockHistogramMetric(ctrl)
	metric.EXPECT().Name().Return(metricName)
	metric.EXPECT().Tags().Return(metricTags)
	metric.EXPECT().Type().Return(metrics.Histogram)
	metric.EXPECT().Value().Return(val)

	return metric
}

func havingATimingMetric(ctrl *gomock.Controller, val time.Duration) *metrics.MockTimingMetric {
	metric := metrics.NewMockTimingMetric(ctrl)
	metric.EXPECT().Name().Return(metricName)
	metric.EXPECT().Tags().Return(metricTags)
	metric.EXPECT().Type().Return(metrics.Timing)
	metric.EXPECT().Value().Return(val)

	return metric
}
