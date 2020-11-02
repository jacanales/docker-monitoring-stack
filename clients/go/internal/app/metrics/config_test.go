// +build unit

package metrics_test

import (
	"os"
	"testing"

	"github.com/stretchr/testify/require"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
)

func TestNewConfig(t *testing.T) {
	varName := "DATADOG_ADDR"
	defaultValue := "localhost:8125"

	val, found := os.LookupEnv(varName)
	if !found {
		val = defaultValue
	}

	c := metrics.NewConfig()

	expected := metrics.Config{Addr: val}
	require.Equal(t, expected, c)
}
