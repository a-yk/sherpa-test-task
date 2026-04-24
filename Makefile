UID=$(id -u)
GID=$(id -g)

all: build

build:
	docker compose build
	docker compose up -d
	docker exec catalog-master-container sh -c "cd /var/www/app && /usr/bin/composer install --optimize-autoloader"
	docker exec catalog-master-container sh -c "php bin/console doctrine:migrations:migrate -n"
	docker exec catalog-master-container sh -c "cd /var/www/app/frontend && npm install && NG_CLI_ANALYTICS=false npm run build"
	docker exec catalog-master-container sh -c "php bin/console app:bootstrap"
	docker compose down

server-start:
	docker compose up -d

server-stop:
	docker compose down
