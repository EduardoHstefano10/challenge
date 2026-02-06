.PHONY: up down build setup migrate seed fresh export

# Start all containers
up:
	docker compose up -d

# Stop all containers
down:
	docker compose down

# Build containers
build:
	docker compose build

# Full setup: build, start, install deps, migrate, seed
setup: build up
	docker compose exec app composer install
	docker compose exec app php artisan migrate --force
	docker compose exec app php artisan db:seed --force

# Run migrations
migrate:
	docker compose exec app php artisan migrate

# Run seeders
seed:
	docker compose exec app php artisan db:seed

# Fresh migration + seed
fresh:
	docker compose exec app php artisan migrate:fresh --seed

# Test export endpoint
export:
	curl -OJ "http://localhost:8000/api/credit-report/export"
