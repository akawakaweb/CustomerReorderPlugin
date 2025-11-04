DOCKER=docker compose
EXEC_PHP=$(DOCKER) exec php
RUN_NODE=$(DOCKER) run --rm nodejs

phpunit:
	$(EXEC_PHP) vendor/bin/phpunit

phpspec:
	$(EXEC_PHP) vendor/bin/phpspec run --ansi --no-interaction -f dot

phpstan:
	$(EXEC_PHP) vendor/bin/phpstan analyse

psalm:
	$(EXEC_PHP) vendor/bin/psalm

behat-js:
	$(EXEC_PHP) vendor/bin/behat --colors --strict --no-interaction -vvv -f progress

install:
	$(EXEC_PHP) composer install --no-interaction --no-scripts

backend:
	$(EXEC_PHP) tests/Application/bin/console sylius:install --no-interaction
	$(EXEC_PHP) tests/Application/bin/console sylius:fixtures:load default --no-interaction

frontend:
	$(RUN_NODE) `(cd tests/Application && yarn install --pure-lockfile)`
	$(RUN_NODE) `(cd tests/Application && yarn build)`

behat:
	$(EXEC_PHP) vendor/bin/behat --colors --strict --no-interaction -vvv -f progress

init: install backend frontend

ci: init phpstan psalm phpunit phpspec behat

integration: init phpunit behat

static: install phpspec phpstan psalm
