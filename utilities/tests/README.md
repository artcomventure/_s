# Tests

Playwright browser tests: utilities tests (`THEME/utilities/tests/`) and optional project tests (`THEME/tests/`).

## Requirements

- Run on the host (not in Docker), in the theme folder.
- Google Chrome installed (no Playwright browser download needed).
- `npm install` (installs `@playwright/test`).
- For `dev`: Docker containers running.

## Usage

```sh
npm test                                 # against DEV_URL
TEST_ENV=stage npm test                  # against STAGE_URL (or `live`)
BASE_URL=http://localhost npm test       # explicit URL
npm test -- smoke.spec.mjs               # single file (any Playwright CLI option after `--`)
npm run test:report                      # open HTML report
```

URLs and test options (e.g. `TEST_PATHS`) come from the nearest `docker/.env` above the theme (`DEV_URL`, `STAGE_URL`, `LIVE_URL`), overridden by `docker/.env.local` and the shell.

Tests tagged `@write` change the database and only run against `DEV_URL`.

Options for all page tests (shell or `docker/.env`):

- `TEST_PATHS="/ /blog/ /?s=test /404-check/"` – pages to test (default `/`).
- `STAGE_AUTH=user:password` – HTTP auth (also `LIVE_AUTH`, `DEV_AUTH`); put it in `docker/.env.local`, not in `.env`.

## Smoke test

`smoke.spec.mjs` checks each page for JS errors, failed requests, PHP errors in the HTML and theme scripts without `defer`, plus pjax navigation (link click and `history.back()` without full reload).

```sh
SMOKE_COOKIES=cookies.json npm test   # as logged-in admin
```

Admin cookies (valid for 1 hour, run in the project's `docker/`):

```sh
docker-compose exec -T wordpress wp eval-file - --path=/var/www/html --allow-root < ~/Develop/wordpress/wp-content/themes/_s/bin/admin-cookies.php > cookies.json
```

`SMOKE_COOKIES` is relative to the theme folder, e.g. `../../../../docker/cookies.json`. Don't commit the cookies file.

## Privacy test

`privacy.spec.mjs` loads each page in a fresh browser (nothing accepted) and fails on external requests and cookies before consent.

- `PRIVACY_ALLOW="ccm.jivo.group"` – external requests allowed before consent, URL parts (e.g. the consent tool itself).
- `PRIVACY_COOKIES="pll_language"` – cookies allowed before consent, names (technically necessary only).

## SEO test

`seo.spec.mjs` checks each page for title, `<html lang>`, exactly one `<h1>`, hreflang on own domain; pages with status 200 also for meta description and canonical on own domain; error pages for `noindex`. Plus:

- unknown URLs return 404 (no soft 404),
- `live`: pages indexable, `robots.txt` doesn't block everything,
- `stage`: not indexable for crawlers (HTTP auth or `noindex`, checked without credentials).

## Links test

`links.spec.mjs` checks every internal link on each page: status `>= 400` fails, redirects are warnings (better link the final URL). Each URL is checked once per run. External links are not checked (flaky, rate limits).

- `LINKS_EXCLUDE="/feed/ /download/"` – URL parts to skip (always skipped: `/wp-admin`, `/wp-login`, `logout`).

## Accessibility test

`a11y.spec.mjs` checks each page (mobile 390px, desktop 1440px) with [axe-core](https://github.com/dequelabs/axe-core) against WCAG 2.2 A/AA. Violations with impact `critical`/`serious` fail, others are warnings; checks axe can't decide (e.g. contrast on images) are listed as `review` in the report.

- `A11Y_IMPACT="critical serious"` – impacts that fail the test (stricter: `critical serious moderate minor`).
- `A11Y_EXCLUDE=".ccm-root"` – elements to skip, selectors (third-party widgets you can't fix).

Automatic checks find only part of the problems (no keyboard flow, screen reader output, meaningful alt texts).

## Mail test

`mail.spec.mjs` (tag `@write`, `dev` only) fills and sends Contact Form 7 forms and checks the mail arrives in Mailpit (test mails are deleted afterwards).

- `MAIL_FORMS="/contact/ /registration/"` – pages with a CF7 form (none by default: no test).
- `MAILPIT_URL=http://localhost:8025` – Mailpit (default).
- `MAIL_DELAY=5` – seconds before submit; spam protection (CF7 honeypot time check) rejects faster forms.

Values are set by script, not typed (consent dialogs trap the focus). On failure the CF7 status shows why (`validation_failed` incl. fields, `spam`, `mail_failed`).

## Visual test

`visual.spec.mjs` (tag `@visual`, not part of `npm test`) compares full-page screenshots (mobile 390px, desktop 1440px) with reference screenshots, usually from `live`:

```sh
npm run test:visual:reference   # 1. create reference from live (overwrites it, compares nothing)
npm run test:visual             # 2. compare dev with reference
npm run test:report             # 3. differences: expected / actual / diff
```

Reference from `stage` instead (e.g. before a deployment, needs `STAGE_AUTH`): `npm run test:visual:reference:stage`.

Reference screenshots are stored in `visual-snapshots/` next to this README (not committed, recreate before each comparison).

Only meaningful with the same content: import a current `live` DB dump into `dev` first (`importDB`).

Options (shell or `docker/.env`):

- `VISUAL_PATHS="/ /about/"` – pages to compare (default `TEST_PATHS`).
- `VISUAL_MASK="video, iframe"` – dynamic elements to cover (default `video, iframe`).
- `VISUAL_BLOCK="ccm.jivo.group"` – requests to block, URL parts (e.g. consent banners).
- `VISUAL_THRESHOLD=0.01` – allowed ratio of different pixels (default `0.01`).

Elements hidden in screenshots (e.g. dev info panel): `visual.css`.

## Project tests

Put `*.spec.mjs` files in `THEME/tests/`; they run as Playwright project `project` next to `utilities`.
