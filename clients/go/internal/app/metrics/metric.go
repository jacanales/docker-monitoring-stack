//go:generate go run -mod=mod github.com/golang/mock/mockgen -source=$GOFILE -destination=./mock_$GOFILE -package=$GOPACKAGE -self_package=$GOPACKAGE

package metrics

import (
	"fmt"
	"time"
)

type MetricType uint

const MetricPrefix = "company.project."

const (
	Gauge MetricType = iota
	Count
	Increment
	Decrement
	Histogram
	Timing
)

type Metric interface {
	Name() string
	Tags() map[string]string
	Type() MetricType
}

type GaugeMetric interface {
	Metric
	Value() float64
}

type HistogramMetric interface {
	Metric
	Value() float64
}

type CountMetric interface {
	Metric
	Value() int64
}

type TimingMetric interface {
	Metric
	Value() time.Duration
}

type Writer interface {
	Send(Metric) error
}

func BuildMetricName(m Metric) string {
	return fmt.Sprintf("%s%s", MetricPrefix, m.Name())
}
