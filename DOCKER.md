# Local development with Docker

A local stack for the wiki: PHP 8.3 + Apache, MariaDB, and phpMyAdmin.

## Prerequisites

- Docker Desktop

## One-time setup

1. **Environment file** — the app reads `.env` (gitignored) from the project
   root. Either copy the example:

   ```
   cp .env.example .env
   ```

   or, if you already have a `.env`, make sure its database values point at the
   local container, not the server:

   ```
   DB_HOST=db
   DB_USER=wiki
   DB_PASSWORD=wiki
   DB_NAME=wiki
   BASE_URL=http://localhost:8090
   COOKIE_DOMAIN=localhost
   ```

   ⚠️ If `DB_HOST` still points at the Hetzner server, the local container will
   read and write the **production** database. Change it.

2. **Database schema** — drop a SQL dump from the server into
   `docker/db-init/` (see the README there). Imported automatically on first
   start.

## Run

```
docker compose up -d --build
```

- App: <http://localhost:8090>
- phpMyAdmin: <http://localhost:8091> (server `db`, user `wiki` / `wiki`, or
  root / `root`)
- MariaDB is also exposed on `localhost:3307` for external DB tools (3306 is
  usually taken by a local XAMPP/MySQL).

The first boot runs `composer install` inside the container (a minute or so).

## Common commands

```
docker compose up -d --build   # start (rebuild image)
docker compose logs -f web      # follow web logs
docker compose down             # stop
docker compose down -v          # stop AND wipe the database volume
docker compose exec web bash    # shell inside the PHP container
```

## Notes

- **Login cookies** are `Secure`; modern browsers accept them over
  `http://localhost`. If a login doesn't persist, that's the first thing to
  check.
- Git deploys **code only** — the database, uploaded images
  (`externalImages/`), and `.env` are handled separately.
- Reset a broken local DB with `docker compose down -v` and start again.
