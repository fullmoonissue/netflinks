# Netflinks

This is the tool I use to create NLs (pdf documents containing images and links) for my friends.

But it's also my QA sandbox project (beginnings with Playwright, diátaxis documentation, ...).

For the moment, I execute this project locally (no docker, no login / pwd, ...).

## Origin

Netflix + Links = Netflinks

## Prerequisites

- Composer ([🔗](https://getcomposer.org/download/))
- Symfony CLI ([🔗](https://symfony.com/download#step-1-install-symfony-cli))
- NPM ([🔗](https://nodejs.org/en/download))

## Install

- git clone https://github.com/fullmoonissue/netflinks
- cd netflinks
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

## Roadmap

Here are some ideas that I will implement from time to time :

- (feat:stack) try php 8.4 and those new features (property hook, asymmetric visibility, ...)
- (feat:stack) dockerize the project
- (feat:code) increase phpstan level and add psalm
- (feat:code) fix deprecations
- (feat:code) find a way to pull out from the fixed version of twig and eab
- (feat) add a CI
- (feat) create a favicon
- (feat) add the possibility to do a backup of the database
- (feat) add the possibility to import / export links
- (feat) add the possibility to archive old links
- (chore) do some code cleaning
- (docs) create a schema containing all the notions of the project
- (docs) add a writing if an identification (login / pwd) wants to be set
- (docs) add some additional texts in the `Explanations` and `Guides` parts
- (docs) write the english part of the diátaxis documentation
- (test) add more playwright tests
