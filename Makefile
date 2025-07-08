
.PHONY: install
install:
	docker compose build --build-arg=$(shell id -u) || exit 0
	docker compose up -d || exit 0
	docker compose exec abstract-mold composer install || exit 0

.PHONY: build
build:
	docker compose build --tag=asokol1981/abstract-mold --build-arg=$(shell id -u) || exit 0

.PHONY: start
start:
	docker compose up -d || exit 0

.PHONY: stop
stop:
	docker compose down || exit 0

.PHONY: uninstall
uninstall:
	docker compose down --remove-orphans --volumes || exit 0
	docker rmi asokol1981/abstract-mold:latest || exit 0

.PHONY: exec
exec:
	docker compose exec -it abstract-mold $(filter-out $@,$(MAKECMDGOALS)) || exit 0

.PHONY: composer
composer:
	docker compose exec abstract-mold composer $(filter-out $@,$(MAKECMDGOALS)) || exit 0

.PHONY: test
test:
	docker compose exec abstract-mold php vendor/bin/phpunit $(filter-out $@,$(MAKECMDGOALS)) --coverage-text || exit 0

.PHONY: coverage
coverage:
	docker compose exec abstract-mold php vendor/bin/phpunit $(filter-out $@,$(MAKECMDGOALS)) --coverage-html coverage || exit 0

.PHONY: artisan
artisan:
	docker compose exec abstract-mold php artisan $(filter-out $@,$(MAKECMDGOALS))

.PHONY: php-cs-fixer
php-cs-fixer:
	docker compose exec abstract-mold vendor/bin/php-cs-fixer fix

# https://docs.codecov.com/docs/codecov-yaml
.PHONY: codecov-validate
codecov-validate:
	curl -X POST --data-binary @codecov.yml https://codecov.io/validate

# This empty rule prevents "make" from throwing an error
# when extra arguments (like "bash") are passed as targets.
%:
	@: