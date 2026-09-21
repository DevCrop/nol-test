-include .env

COMPOSE_PROJECT_NAME ?= bluesquare260918
WEB_PORT ?= 8628
DB_HOST_PORT ?= 3328
PMA_PORT ?= 9628

up:
	docker compose up -d --build web db
	@$(MAKE) --no-print-directory status

up-tools:
	docker compose --profile tools up -d --build web db phpmyadmin
	@$(MAKE) --no-print-directory status

status:
	@echo Public: http://localhost:$(WEB_PORT)
	@echo Admin:  http://gate.local:$(WEB_PORT)/admin
	@echo MySQL:  127.0.0.1:$(DB_HOST_PORT)
	@echo PMA:    http://localhost:$(PMA_PORT)

db-import:
	docker cp 260918.sql $$(docker compose ps -q db):/tmp/260918.sql
	docker compose exec -T db sh -lc 'mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" --default-character-set=utf8mb4 < /tmp/260918.sql'
	@$(MAKE) --no-print-directory migrate

migrate:
	docker compose exec -T web php sql/migrate.php up

qa:
	docker compose exec -T web php qa/run-all.php

lint:
	docker compose exec -T web php qa/php-lint.php

logs:
	docker compose logs --tail=200 web db

down:
	docker compose --profile tools down

reset:
	docker compose --profile tools down -v --remove-orphans
