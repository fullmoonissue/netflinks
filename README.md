# Netflinks

This is the tool I use to create NLs (pdf documents containing images and links) for my friends.

But it's also my QA sandbox project (beginnings with Playwright, diátaxis documentation, ...).

For the moment, I execute this project locally (no docker, no login / pwd, ...).

## Origin

Netflix + Links = Netflinks

## Prerequisites

- Composer ([🔗](https://getcomposer.org/download/))
- Symfony CLI ([🔗](https://symfony.com/download#step-1-install-symfony-cli))

## Install

- git clone https://github.com/fullmoonissue/netflinks
- cd /path/to/netflinks
- make install
- ./bin/console db:create
- ./bin/console doctrine:migration:migrate

## Run

- make server-start
- go to https://127.0.0.1:8000/admin/en (_en_ or _fr_ at the end switch your preferred language)

## Tests

- make test (launch php tests)
- make pw-test (launch playwright tests)
- make ci-qualification (launch quality code checks and all tests)

## Documentation

When Netflinks is launched, you'll find a [diátaxis](https://diataxis.fr/) documentation in the dashboard.
