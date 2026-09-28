-include .env

COMPOSE_PROJECT_NAME ?= noltheater
WEB_PORT ?= 8618
DB_HOST_PORT ?= 3318
PMA_PORT ?= 9618

up:
	docker compose up -d --build web db
	@$(MAKE) --no-print-directory status

build:
	docker compose build web

up-tools:
	docker compose --profile tools up -d --build web db phpmyadmin
	@$(MAKE) --no-print-directory status

status:
	@echo ===========================================================
	@echo   Web        http://localhost:$(WEB_PORT)
	@echo   phpMyAdmin http://localhost:$(PMA_PORT)  — make up-tools
	@echo   MySQL      127.0.0.1:$(DB_HOST_PORT)
	@echo   Admin      http://gate.local:$(WEB_PORT)  (hosts: 127.0.0.1 gate.local)
	@echo   Admin old  http://localhost:$(WEB_PORT)/nol-gate
	@echo ===========================================================

bash:
	docker compose exec -it web bash

stop:
	docker compose --profile tools down

down:
	docker compose --profile tools down

db-import:
	docker cp 260918.sql $$(docker compose ps -q db):/tmp/260918.sql
	docker compose exec -T db mysql -uroot -proot --default-character-set=utf8mb4 -e "source /tmp/260918.sql"
	docker compose exec -T db mysql -uroot -proot -e "GRANT ALL ON dbusrdaehakro0605.* TO 'user'@'%'; FLUSH PRIVILEGES;"
	@$(MAKE) --no-print-directory migrate

migrate:
	docker compose exec -T web php sql/migrate.php up

migrate-status:
	docker compose exec -T web php sql/migrate.php status

migrate-down:
	docker compose exec -T web php sql/migrate.php down

migrate-make:
	docker compose exec -T web php sql/migrate.php make "$(NAME)"

reset:
	docker compose --profile tools down -v --remove-orphans

help:
	@echo "  make up         PHP 7.4 + MySQL 8.0"
	@echo "  make db-import  루트 260918.sql 적재"
	@echo "  make migrate        sql/migrations 적용"
	@echo "  make migrate-status 적용/미적용 목록"
	@echo "  make migrate-down   마지막 마이그레이션 롤백"
	@echo "  make migrate-make NAME=foo  빈 up/down 생성"
	@echo "  make up-tools   + phpMyAdmin"
	@echo "  make bash       web 컨테이너"
	@echo "  make down       중지"
	@echo "  make reset      볼륨까지 삭제"
