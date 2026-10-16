# docker-monitoring-stack

Self-hosted monitoring stack: a StatsD/collectd-style metric pipeline for container and host
metrics, fronted by Traefik, with two example clients (Go and PHP) that push application metrics
into it.

- **Purpose**: collect host + application metrics, store them as time series, and visualize them.
- **Architecture**: `clients (StatsD/UDP) → Telegraf → InfluxDB 3 Core ← Grafana`, with Traefik as the
  HTTP entrypoint for Grafana and a `whoami` probe service.
- **Entrypoint**: `make up` (see [Getting started](#getting-started)).

## Architecture

```
                      ┌──────────────── metrics plane ─────────────────┐
   app / CLI client   │                                                │
   ┌────────────┐     │   ┌──────────┐        ┌────────────────────┐   │
   │ PHP client │─────┼──▶│ Telegraf │───────▶│ InfluxDB 3 Core    │   │
   │ Go client  │ UDP │   │ :8125    │  HTTP  │ :8181              │   │
   └────────────┘ :8125│  │ statsd   │  :8181 │ database: telegraf │   │
                       │  │ inputs   │        └─────────┬──────────┘   │
                       │  └──────────┘                  │ SQL          │
                       └────────────────────────────────┼──────────────┘
                                                        ▼
                       ┌────────── http plane ────┐  ┌──────────┐
   browser ──────────▶ │ Traefik :80  (Docker)    │─▶│ Grafana  │
                       │ whoami :80               │  │ :3000    │
                       └──────────────────────────┘  └──────────┘
```

- **Metrics plane** (`backend` network): Telegraf scrapes host metrics and listens for StatsD
  packets on UDP `8125`; it writes the resulting line protocol into InfluxDB 3 Core over HTTP.
  InfluxDB and Telegraf are not reachable from the host network beyond the ports published below.
- **HTTP plane** (`traefik` network): Traefik discovers containers through the Docker socket and
  routes by `Host(...)` rules. Grafana is attached to both networks so it can be routed by Traefik
  and still query InfluxDB over `backend`.
- **Clients**: sample implementations of the StatsD wire protocol live under `clients/` and are not
  part of the compose file. They exist to push application metrics into Telegraf.

## Services

| Service | Image | Why it is deployed | Ports | Networks | Volumes |
| --- | --- | --- | --- | --- | --- |
| `traefik` | `traefik:v3.7.15` | HTTP reverse proxy / edge router. Reads the Docker provider and publishes routers from the labels of containers that opt in with `traefik.enable=true`, so no per-service proxy config is needed. Also serves its own API/dashboard. | `80:80`, `8080:8080` | `traefik` | `/var/run/docker.sock` (ro), `config/traefik/traefik.yml` (ro) |
| `whoami` | `traefik/whoami:v1.12.0` | Trivial HTTP echo service used as a probe to verify that Traefik routing, the Docker provider and the `traefik` network work end to end. | – (internal) | `traefik` | – |
| `influxdb` | `influxdb:3.12.1-core` | Time-series database. Stores everything Telegraf writes, and is the query backend Grafana reads from. Runs InfluxDB 3 Core with a file-backed object store and a preconfigured admin token. | `8181:8181` | `backend` | `influxdb3:/var/lib/influxdb3`, secret `influxdb-admin-token` (ro) |
| `influxdb-init` | `influxdb:3.12.1-core` | One-shot bootstrap: waits for InfluxDB to become healthy and creates the `telegraf` database. InfluxDB 3 never auto-creates a database on write, so this must run once per fresh volume. Idempotent, exits after the create attempt. | – | `backend` | – |
| `telegraf` | `telegraf:1.40.2` | Metric collector and StatsD server. Scrapes host metrics through the `cpu`, `disk`, `diskio`, `kernel`, `mem`, `net`, `netstat`, `processes` and `system` inputs, receives StatsD over UDP, and writes line protocol into InfluxDB 3. | `8125:8125/udp` | `backend` | `./config/telegraf/telegraf.conf` (ro), `./config/telegraf/bin` |
| `grafana` | `grafana/grafana:13.2.4` | Visualization and dashboards. Queries InfluxDB over the `backend` network, and is published through Traefik at `grafana.localhost`. | `3000:3000` | `traefik`, `backend` | `grafana:/var/lib/grafana` |

### Why each service exists

- **Traefik** — the old stack resolved Grafana through a mix of Traefik v1 and v2 labels. Traefik is
  kept because it makes the stack reachable by hostname (`grafana.localhost`,
  `whoami.docker.localhost`) without publishing extra ports or hand-writing proxy rules.
- **whoami** — there is no other trivial route to test the proxy with; it is the smoke test for the
  `traefik` network and the Docker provider.
- **InfluxDB** — the storage layer. Telegraf is only a write path; without InfluxDB nothing can be
  queried.
- **influxdb-init** — InfluxDB 3 Core requires an explicit database creation, so a one-shot container
  does it once and exits. It replaces the `INFLUXDB_DB` bootstrap that InfluxDB 1.x performed from
  environment variables.
- **Telegraf** — the collector. It owns the StatsD listener the clients talk to and the host-metric
  inputs, so it is the only service that needs access to the container's own `/proc`/`/sys` view.
- **Grafana** — the read path for humans. It is the only service on both networks, because it must be
  routed by Traefik and must reach InfluxDB.

## Getting started

Requirements: Docker with Compose v2, GNU Make, `openssl`.

```bash
make token   # generate config/influxdb3/admin-token.json + .env (once; both are gitignored)
make up      # start the stack
make down    # stop and remove containers
```

`make token` is mandatory before the first `make up`: InfluxDB 3 Core starts with
`--admin-token-file`, and the stack refuses to boot without the token file. The command writes two
files that are **never committed**:

| File | Consumer |
| --- | --- |
| `config/influxdb3/admin-token.json` | InfluxDB, mounted as the `influxdb-admin-token` Docker secret |
| `.env` | Compose interpolation, so Telegraf gets `INFLUXDB3_AUTH_TOKEN` |

Regenerating the token invalidates the old one, so it requires dropping the old files:

```bash
make down
rm -f config/influxdb3/admin-token.json .env
make token && make up
```

Afterwards the new token has to be entered in Grafana again.

### Endpoints

| URL | What |
| --- | --- |
| http://grafana.localhost | Grafana (admin / `GrafanaPassword`) |
| http://localhost:8080 | Traefik dashboard (unsecured, local development only) |
| http://whoami.docker.localhost | `whoami` probe |
| http://localhost:3000 | Grafana, direct |
| http://localhost:8181 | InfluxDB 3 Core HTTP API |

`grafana.localhost` and `whoami.docker.localhost` resolve to `127.0.0.1` on most systems; if they do
not, add them to `/etc/hosts`.

## Connecting Grafana to InfluxDB 3

Grafana is not provisioned with a data source, so configure it once in the UI:
**Connections → Data sources → Add data source → InfluxDB**:

| Field | Value |
| --- | --- |
| Query language | `SQL` |
| URL | `http://influxdb:8181` |
| Authentication | `With Credentials` / token |
| Token | the value of `token` in `config/influxdb3/admin-token.json` |
| Database | `telegraf` |

InfluxDB 3 no longer supports Flux, and InfluxQL is only a compatibility surface; use SQL for new
panels.

## Verifying the pipeline

```bash
# 1. send a StatsD metric by hand
printf 'demo.counter:1|c' | nc -u -w0 localhost 8125

# 2. query InfluxDB 3 for it (Telegraf flushes every 10s)
docker compose exec influxdb influxdb3 query \
  --database telegraf \
  --token "$(sed -n 's/^INFLUXDB3_AUTH_TOKEN=//p' .env)" \
  "SELECT * FROM demo_counter ORDER BY time DESC LIMIT 5"
```

A metric name is stored as the table of the same name with `.` and `-` replaced by `_`, and the
StatsD metric type is kept in the `metric_type` column (`counter`, `gauge`, `timing`, `histogram`,
`set`, `distribution`).

## Known limitation: StatsD tags are dropped

`config/telegraf/telegraf.conf` leaves the statsd input at `datadog_extensions = false`. The Datadog
extensions carry two things this stack's clients send: the `|#tag:value` tag section and the `d`
(distribution) and `h` (histogram) metric types. With the extensions off, Telegraf logs
`Splitting '|', unable to parse metric: ...` for every tagged datagram and stores the sample without
its tags, and the `d`/`ms` samples never arrive - the Go and PHP clients send both.

Setting `datadog_extensions = true` restores tags and those two types; it is not the default here
because it changes the shape of the stored metrics (tags become columns).

## Notes on the InfluxDB 3 migration

- **The Processing Engine is disabled.** The official `influxdb:3-core` image enables it with
  `INFLUXDB3_PLUGIN_DIR=/plugins`, which makes the server initialize a Python environment at
  startup. This stack ships no plugins, and setting the variable to an empty string does *not*
  disable the engine, so the compose file unsets it through a custom entrypoint. Remove that
  entrypoint if you want to use plugins.
- **Databases are not auto-created.** InfluxDB 1.x created `INFLUXDB_DB` for you; InfluxDB 3
  requires an explicit `influxdb3 create database`, which the `influxdb-init` service performs once
  the server is healthy. It is safe to run on every `make up`: an existing database reports
  `409 Conflict` and the service treats that as success.
- **The InfluxDB 1.x data is not migrated.** The compose file uses a new volume (`influxdb3`) so
  the old `influxdb` volume is left untouched; InfluxDB 3 cannot read InfluxDB 1.x shards. Remove
  it yourself once you are sure you do not need it: `docker volume rm <project>_influxdb`.

## Clients

| Client | Path | Stack |
| --- | --- | --- |
| Go | `clients/go` | Go 1.26, `github.com/DataDog/datadog-go/v5` StatsD client |
| PHP | `clients/php` | PHP >= 8.4.1, `slickdeals/statsd` 3.2 StatsD connection |

Both implement the same metric model (counter, gauge, timing, histogram, distribution, set) and ship
tests: `go test ./...` in `clients/go`, and `composer install && vendor/bin/phpunit` in
`clients/php`. The PHP client follows the `domnikl/statsd` package it was written against: that
package is no longer installable (its repository is gone), so the dependency resolves to
`slickdeals/statsd`, the successor Packagist names, which keeps the `Domnikl\Statsd` namespace and
the same `Connection` interface.

## Configuration

| File | Purpose |
| --- | --- |
| `docker-compose.yml` | The stack definition. |
| `config/telegraf/telegraf.conf` | Telegraf agent, inputs, StatsD listener and the InfluxDB 3 output. |
| `config/traefik/traefik.yml` | Traefik static configuration (entrypoints, Docker provider, API). |
| `config/influxdb3/admin-token.example.json` | Schema of the offline admin token file generated by `make token`. |
| `clients/go`, `clients/php` | Example StatsD clients. |

## Security notes

This stack is a local development environment and is **not hardened**:

- InfluxDB publishes `8181` on all interfaces and Grafana's admin password is a literal in
  `docker-compose.yml`.
- Traefik runs with `--api.insecure=true`, so its dashboard is unauthenticated on `:8080`.
- `providers.docker.exposedByDefault` is `false`, so exactly the containers that set
  `traefik.enable=true` are published. Flipping it to `true` would also publish containers of
  unrelated Compose projects sharing this Docker daemon, because port 80 belongs to this Traefik.
- Grafana's `GF_SERVER_ROOT_URL` points at `http://grafana.localhost`, so it must be reached over
  plain HTTP. Change it together with the Traefik entrypoint if you add TLS.
- Only Traefik gets the Docker socket (read-only); no other service in the stack needs it.

Before exposing any of this beyond localhost, remove `--api.insecure`, bind published ports to
`127.0.0.1`, move the Grafana admin password to a secret, and put a TLS-terminating proxy in front.
