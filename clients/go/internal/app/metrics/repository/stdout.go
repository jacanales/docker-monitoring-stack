package repository

import (
	"fmt"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
)

type InMemoryWriter struct {
	Metrics []string
}

func (w *InMemoryWriter) Write(p []byte) (n int, err error) {
	w.Metrics = append(w.Metrics, string(p))

	return len(p), nil
}

func (w *InMemoryWriter) Send(m metrics.Metric) error {
	str := fmt.Sprintf("Name: \t%v\nTags:\t%v\nType:\t%v\n",
		metrics.BuildMetricName(m), buildTags(m.Tags()), m.Type(),
	)

	_, err := fmt.Fprint(w, str)
	if err != nil {
		return err
	}

	return nil
}
