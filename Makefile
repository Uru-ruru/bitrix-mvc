SHELL := /bin/sh

# Composer is already available in this image
COMPOSER_IMAGE ?= composer:2
PHP_IMAGE ?= php:8.4-cli
WORKDIR ?= /app

# Persist composer cache between runs (stored in the project folder)
COMPOSER_CACHE_DIR ?= $(WORKDIR)/.composer-cache

.PHONY: composer-install composer-update composer-validate composer-clear-cache test

composer-install:
	docker run --rm -t \
		-v "$(CURDIR):$(WORKDIR)" \
		-w "$(WORKDIR)" \
		-e COMPOSER_CACHE_DIR="$(COMPOSER_CACHE_DIR)" \
		$(COMPOSER_IMAGE) \
		composer install --no-interaction --prefer-dist

composer-update:
	docker run --rm -t \
		-v "$(CURDIR):$(WORKDIR)" \
		-w "$(WORKDIR)" \
		-e COMPOSER_CACHE_DIR="$(COMPOSER_CACHE_DIR)" \
		$(COMPOSER_IMAGE) \
		composer update --no-interaction --prefer-dist

composer-validate:
	docker run --rm -t \
		-v "$(CURDIR):$(WORKDIR)" \
		-w "$(WORKDIR)" \
		-e COMPOSER_CACHE_DIR="$(COMPOSER_CACHE_DIR)" \
		$(COMPOSER_IMAGE) \
		composer validate --no-interaction

composer-clear-cache:
	docker run --rm -t \
		-v "$(CURDIR):$(WORKDIR)" \
		-w "$(WORKDIR)" \
		$(COMPOSER_IMAGE) sh -lc '\
			rm -rf .composer-cache || true; \
			mkdir -p .composer-cache \
		'

test:
	docker run --rm -t \
		-v "$(CURDIR):$(WORKDIR)" \
		-w "$(WORKDIR)" \
		$(PHP_IMAGE) \
		./vendor/bin/phpunit --display-deprecations --display-warnings --configuration ./tests/phpunit.xml.dist

cs-fixer:
	docker run --rm -t \
    		-v "$(CURDIR):$(WORKDIR)" \
    		-w "$(WORKDIR)" \
    		$(PHP_IMAGE) \
    		php ./vendor/bin/php-cs-fixer fix --verbose --allow-risky=yes