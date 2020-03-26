package metric

type Tags = map[string]string

type Repository interface {
	StartTimer(name string)
	StopTimer(name string, tags Tags)
	Count(name string, value int64, tags Tags)
	Gauge(name string, value float64, tags Tags)
	Histogram(name string, value float64, tags Tags)
	Distribution(name string, value float64, tags Tags)
	Set(name string, value string, tags Tags)
}
