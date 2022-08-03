FROM ghcr.io/roadrunner-server/roadrunner:2.10.7 AS roadrunner
FROM php:8.1-cli-alpine 

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

RUN apk update && apk add --no-cache \
	vim \
	libzip-dev \
	unzip \
	bash \
	postgresql-dev

RUN docker-php-ext-install zip \
	&& docker-php-ext-install sockets \
	&& docker-php-ext-install opcache \
	&& docker-php-ext-enable opcache \
	&& docker-php-ext-install pdo pdo_pgsql

COPY . /app
WORKDIR /app
CMD ["/usr/local/bin/rr", "serve", "-d", "-c", "/app/.rr.yaml"]
