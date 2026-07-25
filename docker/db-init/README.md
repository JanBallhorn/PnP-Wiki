# Database initialisation

Put your database dump here as a `.sql` file (e.g. `01-schema.sql`). MariaDB
imports every `*.sql` / `*.sh` in this directory **once**, on the first start
with an empty data volume.

## Getting the dump from the server

In phpMyAdmin on the server: **Export** → format **SQL**. Include the table
structure, and data if you want real content locally. Under the custom options,
**do not** tick "Add CREATE DATABASE / USE" — the dump should contain only
tables so it imports into the local `wiki` database.

(Or via SSH: `mysqldump --no-create-db <dbname> > dump.sql`.)

## Applying it

- Fresh start: drop the file in here, then `docker compose up` (with an empty
  `db-data` volume) imports it automatically.
- Already ran before? Reset the volume first:
  `docker compose down -v && docker compose up`.

## Pending schema changes

If the export predates a not-yet-deployed change, apply it locally too. Current
pending item:

```sql
ALTER TABLE users ADD COLUMN test_user TINYINT(1) NOT NULL DEFAULT 0;
```

`*.sql` files here are gitignored (a dump may contain data) — only this README
is tracked.
