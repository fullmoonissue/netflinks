# ### ### #
# Install #
# ### ### #

install:
	composer install
	npm install

# ### #
# Run #
# ### #

server-start:
	@symfony server:start

# ### ### #
# Quality #
# ### ### #

# All
ci-qualification:
	make composer-normalize
	make twig-lint
	make rector
	make cs-fixer
	make phpstan
	make test
	make pw-test
	make deptrac

# Composer
composer-normalize:
	composer normalize

# Twig
twig-lint:
	php bin/console lint:twig templates

# Rector
rector:
	php vendor/bin/rector process

# Code style
cs-fixer:
	php vendor/bin/php-cs-fixer fix --allow-risky=yes

# PHPStan
phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=-1

# Tests (PHPUnit)
# >>> Can't use "doctrine:database:create" for sqlite db ...
# APP_ENV=test php bin/console doctrine:database:create
# <-- Operation "Doctrine\DBAL\Platforms\SQLitePlatform::getCreateDatabaseSQL" is not supported by platform.
# APP_ENV=test php bin/console doctrine:database:create --if-not-exists
# <-- Operation "Doctrine\DBAL\Platforms\AbstractPlatform::getListDatabasesSQL" is not supported by platform.
# <<< So, I create the file myself
test:
	@APP_ENV=test php bin/console doctrine:database:drop --force -q
	@APP_ENV=test php bin/console db:destroy
	@APP_ENV=test php bin/console db:create
	@APP_ENV=test php bin/console doctrine:migration:migrate -q
	@php vendor/bin/phpunit tests
	@APP_ENV=test php bin/console db:destroy

# Tests (Playwright)
pw-test: # Runs the end-to-end tests.
	@npx playwright test --project=firefox

pw-test-ui: # Starts the interactive UI mode.
	@npx playwright test --ui --project=chromium

pw-test-debug: # Runs the tests in debug mode.
	@npx playwright test --debug

pw-codegen: # Auto generate tests with Codegen.
	@npx playwright codegen

# Hexagonal architecture
deptrac:
	vendor/bin/deptrac
