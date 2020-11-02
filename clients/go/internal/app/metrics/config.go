package metrics

import (
	"github.com/kelseyhightower/envconfig"
)

type Config struct {
	Addr string `default:"localhost:8125" envconfig:"DATADOG_ADDR"`
}

func NewConfig() Config {
	config := Config{}
	envconfig.MustProcess("", &config)

	return config
}
