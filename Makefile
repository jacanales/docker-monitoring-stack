.ONESHELL:

SHELL := /bin/bash
.SHELLFLAGS := -ec

COMPOSE := docker compose
TOKEN_FILE := config/influxdb3/admin-token.json
ENV_FILE := .env

help:
	@echo 'Usage: make [target] ...'
	@echo
	@echo 'targets:'
	@echo -e "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\\x1b[36m\1\\x1b[m:\2/' | column -c2 -t -s : | sort)"

.DEFAULT_GOAL := help

%:
	@echo 'Invalid target. type `make help` to get a list of available targets'
	@exit 1
force:

token: ## Generate the InfluxDB 3 admin token files needed by `make up`
	@: never silently replace an existing admin token
	if [ -e "$(TOKEN_FILE)" ] || [ -e "$(ENV_FILE)" ]; then
		echo "refusing to overwrite the existing admin token:" >&2
		echo "  $(TOKEN_FILE)" >&2
		echo "  $(ENV_FILE)" >&2
		echo "remove them first if you really want a new token:" >&2
		echo "  rm -f $(TOKEN_FILE) $(ENV_FILE)" >&2
		exit 1
	fi
	umask 077
	# One token is shared by both files; make expands "$$" into "$" for the shell.
	# shellcheck disable=SC2034
	token="apiv3_$$(openssl rand -hex 32)"
	mkdir -p config/influxdb3
	printf '{\n  "token": "%s",\n' "$$token" > "$(TOKEN_FILE)"
	printf '  "name": "_admin",\n' >> "$(TOKEN_FILE)"
	printf '  "description": "local dev admin token"\n}\n' >> "$(TOKEN_FILE)"
	chmod 600 "$(TOKEN_FILE)"
	printf 'INFLUXDB3_AUTH_TOKEN=%s\n' "$$token" > "$(ENV_FILE)"
	chmod 600 "$(ENV_FILE)"
	echo "wrote $(TOKEN_FILE) and $(ENV_FILE)"

up: ## Start the stack
	$(COMPOSE) up -d

down: ## Stop and remove containers
	$(COMPOSE) down
