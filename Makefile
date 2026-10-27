DOCKER_COMP = docker compose
PHP_CONT    = $(DOCKER_COMP) exec php
SYMFONY     = $(PHP_CONT) php bin/console

export UID := $(shell id -u)
export GID := $(shell id -g)

.DEFAULT_GOAL = help
.PHONY: help build up start down logs sh composer console migrate test-db test phpstan cs cs-fix lint ci

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-10s\033[0m %s\n", $$1, $$2}'

build: ## Construit les images
	@$(DOCKER_COMP) build

up: ## Démarre les conteneurs
	@$(DOCKER_COMP) up --detach --wait

start: build up composer-install migrate ## Construit, démarre, installe et migre

composer-install:
	@$(PHP_CONT) composer install --no-interaction

down: ## Arrête les conteneurs
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Affiche les logs
	@$(DOCKER_COMP) logs --tail=50 --follow

sh: ## Shell dans le conteneur PHP
	@$(PHP_CONT) sh

console: ## bin/console, ex : make console c="debug:router"
	@$(eval c ?=)
	@$(SYMFONY) $(c)

migrate: ## Applique les migrations
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction

test-db: ## Crée la base de test et son schéma
	@$(SYMFONY) --env=test doctrine:database:create --if-not-exists --no-interaction
	@$(SYMFONY) --env=test doctrine:migrations:migrate --no-interaction

test: test-db ## Lance les tests, ex : make test c="--filter Converter"
	@$(eval c ?=)
	@$(PHP_CONT) vendor/bin/phpunit $(c)

phpstan: ## Analyse statique
	@$(PHP_CONT) vendor/bin/phpstan analyse --memory-limit=512M

cs: ## Vérifie le style de code
	@$(PHP_CONT) vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Corrige le style de code
	@$(PHP_CONT) vendor/bin/php-cs-fixer fix

lint: ## Lint Twig, YAML et conteneur
	@$(SYMFONY) lint:twig templates
	@$(SYMFONY) lint:yaml config
	@$(SYMFONY) lint:container

ci: cs phpstan lint test ## Toutes les vérifications
