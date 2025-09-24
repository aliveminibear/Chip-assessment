.PHONY: help install test test-coverage lint clean docker-build docker-test docker-shell

help:
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install:
	composer install

test:
	composer test

test-coverage:
	composer test-coverage

lint:
	vendor/bin/phpcs --standard=PSR12 src/ tests/

clean:
	rm -rf vendor/ coverage/

docker-build: 
	docker-compose build

docker-test:
	docker-compose run --rm test

docker-shell: 
	docker-compose run --rm app bash

setup: install

ci: install test