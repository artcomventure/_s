# Docker aka Dev Environment

Goal should be to mirror the production server environment
to prevent as many setup errors as possible.

Config see `.env`.

## Getting started

- [Install Docker](https://www.docker.com/get-started/)
- Add IP and host name `127.0.0.1 {HOST from .env}` to your hosts file
  - macOS: `/etc/hosts`
  - Microsoft: `WINDOWS/system32/drivers/hosts`

## Start containers

- `$ cd /path/to/project/docker`
- `$ docker-compose up -d`
- Open {DEV_URL from .env}

## Import sql

- Put sql file named `db.sql` in docker folder.
- Create terminal for mysql container and call:
- `$ importDB`
- Create terminal for wordpress container.<br />
  Depending on which database you imported call:
- `$ stage2dev` or `$ live2dev`

_For manual import see commands in `.bashrc`._

## MailHog

- http://localhost:8025

---
Last but not least: **Happy Coding!**
