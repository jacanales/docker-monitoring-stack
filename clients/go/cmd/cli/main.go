package main

import (
	"context"
	"fmt"
	"log"
	"time"

	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics"
	"github.com/jacanales/docker-monitoring-stack/internal/app/metrics/repository"
)

func main() {
	cfg := metrics.Config{
		Addr: "localhost:8125",
	}

	cli, err := repository.NewDataDogClient(cfg)
	if err != nil {
		log.Fatal(err)
	}

	wrt := repository.NewStatsDWriter(cli)

	if err := sendMetrics(wrt); err != nil {
		log.Fatal(err)
	}
}

func sendMetrics(writer metrics.Writer) error {
	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()

	tick := time.Tick(500 * time.Millisecond)

	for {
		select {
		case <-tick:
			for _, m := range AppMetrics() {
				if err := writer.Send(m); err != nil {
					return err
				}
			}
			fmt.Println("ping!")
		case <-ctx.Done():
			fmt.Println("done!")
			return nil
		}
	}
}

func AppMetrics() []metrics.Metric {
	return []metrics.Metric{
		GaugeTest{},
		HistogramTest{},
		CountTest{},
		IncrTest{},
		IncrTest{},
		IncrTest{},
		IncrTest{},
		DecrTest{},
		TimingTest{},
	}
}
