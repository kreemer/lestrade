# lestrade

Lestrade is a simplistic web application to track time frames. Initially created to be compliant with [watson](https://github.com/TailorDev/Watson).

## Installation

Lestrade is packaged as a docker container:

```
docker pull ghcr.io/kreemer/lestrade:main

docker run -p 8080:8080 ghcr.io/kreemer/lestrade:main
```

Following is a minimalistic docker-compose excerpt:

```yaml
version: '3.9'

services:
  web:
    image: ghcr.io/kreemer/lestrade:main
    restart: unless-stopped
    environment:
      - APP_ENV=prod
      - DATABASE_URL=postgresql://symfony:password@db:5432/app?serverVersion=13&charset=utf8
      - TZ=Europe/Zurich
    ports:
      - 8080:8080
    depends_on:
      - db

  db:
    image: postgres:13-alpine
    environment:
      POSTGRES_DB: app
      POSTGRES_PASSWORD: password
      POSTGRES_USER: symfony
    volumes:
      - db-data:/var/lib/postgresql/data:rw
      
volumes:
  db-data:
```

After start, you have to initialize the database:

```
docker exec -it lestrade-container php /app/bin/console doctrine:schema:update --force
```

You can create a user with the builtin command:

```
docker exec -it lestrade-container php /app/bin/console app:user-add
```

The user you create will be able to login into the web interface. You will also get an api token, which can be used with [watson](https://github.com/TailorDev/Watson) to sync your times.

## Development

Simply check out this repository and start hacking.
