dev:
	composer install
	npm ci && npm run watch

build:
	rm -r vendor/
	composer install --no-dev --optimize-autoloader
	npm ci
	npm run build
