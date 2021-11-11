dev:
	composer install
	npm ci && npm run watch

build:
	composer install --no-dev --optimize-autoloader
	npm run build
