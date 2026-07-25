# Deployment (git push-to-deploy)

Deploying = pushing to a **bare git repo** on the Hetzner server. A
`post-receive` hook checks the code out into the live directory and runs
`composer install`. `.git` never lives in the web root.

**Git deploys code only.** The database, uploaded images (`externalImages/`),
`.env`, and `imprint.twig` / `privacy.twig` are **not** in git and are handled
separately (see the bottom).

## One-time server setup

```sh
# on the server: create the bare repo (receives pushes)
ssh verpla@www17.your-server.de -p222 'git init --bare ~/wiki.git'
```

```sh
# from your local checkout: copy the deploy hook into the bare repo and make
# it executable. TARGET is already set to /usr/home/verpla/public_html/wiki.
scp -P 222 deploy/post-receive verpla@www17.your-server.de:wiki.git/hooks/post-receive
ssh verpla@www17.your-server.de -p222 'chmod +x ~/wiki.git/hooks/post-receive'
```

`TARGET` in the hook is the directory that already contains `root/`,
`externalImages/` and `.env` on the server. Make sure `composer` and the PHP
CLI are on `PATH` for the SSH user; otherwise the hook warns and you run
`composer install` in `<TARGET>/root` manually.

## One-time local setup

The non-standard SSH port (222) requires the `ssh://` remote form:

```sh
git remote add deploy ssh://verpla@www17.your-server.de:222/usr/home/verpla/wiki.git
```

## Deploy

```sh
git push deploy main
```

The hook output (checkout + composer) streams back into your terminal.

## First deploy — checklist

The current `main` is ahead of what was uploaded by SFTP, so the first push
brings several features live at once. Before (or right after) pushing:

- **DB migration:** the statistics page needs a column that isn't on the server
  yet. Run once on the production DB:
  ```sql
  ALTER TABLE users ADD COLUMN test_user TINYINT(1) NOT NULL DEFAULT 0;
  ```
- **`.env` on the server:** keep the production values — `COOKIE_DOMAIN=<your
  domain>`, `COOKIE_SECURE=true` (or unset), `BASE_URL=https://<your domain>`.
  The hook never touches `.env`.
- Ensure `main` matches what you expect to go live (the checkout overwrites
  tracked files with the pushed version).

## Rollback

Deploy an earlier commit:

```sh
git push deploy <old-commit-sha>:main
```

Or on the server: `git --git-dir=~/wiki.git --work-tree=<TARGET> checkout -f <sha>`.

## What git does NOT deploy — handle separately

| Content | How |
|---|---|
| Database schema/data | phpMyAdmin / `mysqldump` (see `docker/db-init/README.md`) |
| Uploaded images `externalImages/` | `rsync`/`scp` between server and local |
| `.env` secrets | edited per environment, never committed |
| `imprint.twig` / `privacy.twig` | gitignored; live only on the server |
