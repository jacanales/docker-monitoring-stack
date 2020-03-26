package config

import (
	"github.com/kelseyhightower/envconfig"
	"time"
)

type Config struct {
	Host       string        `envconfig:"TELEGRAF_HOST" default:"localhost" required:"true"`
	Port       int           `envconfig:"TELEGRAF_PORT" default:"8125" required:"true"`
	Timeout    time.Duration `envconfig:"TELEGRAF_TIMEOUT" default:"1s" required:"false"`
	Persistent bool          `envconfig:"TELEGRAF_PERSISTENT" default:"false" required:"false"`
	Namespace  string        `envconfig:"DEFAULT_NAMESPACE" default:"jacanales.demo" required:"true"`
}

func Load() *Config {
	var cfg Config
	envconfig.MustProcess("", &cfg)

	return &cfg
}
