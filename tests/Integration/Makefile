PWD = $(shell pwd)

all: up
cleanall: down
re: cleanall all

.PHONY: up
up:
	docker compose \
		-f ./docker-compose.yaml \
		-f ./tracetest/docker-compose.yaml \
		up -d --build

.PHONY: test
test: up
	make tracetest-in-docker command="run test -f ./tests/test-api.yaml"
	make tracetest-in-docker command="run testsuite -f ./tests/testsuite-api.yaml"

.PHONY: tracetest-in-docker
tracetest-in-docker:
	docker run --rm -it \
		-v ${PWD}:${PWD} \
		-w ${PWD}/tracetest \
		--net="host" \
		--entrypoint tracetest \
		kubeshop/tracetest:latest -s http://localhost:11633 ${command}

.PHONY: down
down:
	docker compose \
		-f ./docker-compose.yaml \
		-f ./tracetest/docker-compose.yaml \
		down -v
