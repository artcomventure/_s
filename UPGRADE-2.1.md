# Upgrade auf 2.1: Playwright-Tests, Mailpit

Von 2.0.x auf 2.1.0. **Projekte älter als 2.0:** zuerst [`UPGRADE-2.0.md`](UPGRADE-2.0.md) abarbeiten; vor 1.21 direkt den Kurzweg [`UPGRADE-FROM-1.20.md`](UPGRADE-FROM-1.20.md).
Alle Befehle im Projekt-Theme-Ordner (eine Ebene über `utilities/`) ausführen, **vor** dem Kopieren.

Minor-Version: Nur `utilities/` zu kopieren bricht nichts. Für die Tests und Mailpit braucht es aber Arbeit außerhalb von `utilities/` (Theme-`package.json`, Projekt-`docker/`).

- **Tests:** Playwright in `utilities/tests/` (Smoke, Privacy, SEO, Links, Barrierefreiheit, Mails, Screenshot-Vergleich), Doku in [`utilities/tests/README.md`](utilities/tests/README.md).
- **Docker:** MailHog → Mailpit, `docker/` im Container unter `/var/tmp/docker`, Cache für Live-Medien.
- **ESLint:** gemeinsame Basis `utilities/eslint.base.mjs` für Theme und Utilities.

## Ablauf (Kurzfassung)

| # | Schritt | Wie |
|---|---|---|
| 0 | Vorbedingungen | Projekt < 2.0 → erst `UPGRADE-2.0.md`. Google Chrome installiert. |
| 1 | Prüfen | Skill `utilities-upgrade` bzw. Abschnitt 1 – read-only. |
| 2 | **Kopieren** | `utilities/` komplett aus dem Boilerplate. |
| 3 | Theme | `package.json` (devDependencies, Scripts), `npm install`, `eslint.config.mjs` – Abschnitt 2. |
| 4 | Docker | Mailpit, Mount, `.env.tests` – Abschnitt 2, danach Container neu bauen. |
| 5 | Testen | `npm test`, Funde beheben; Referenz-Screenshots von `live`. |
| 6 | Abnahme | Abschnitt 4. |
| 7 | **Kopieren** | `CHANGELOG.md` – erst ganz am Ende, sonst gilt das Projekt schon als 2.1 und die Prüfungen werden übersprungen. |

## 1. Projekt prüfen

### Docker-Ordner und URLs

Die Tests lesen die URLs aus dem nächsten `docker/.env` oberhalb des Themes (`THEME/docker/` ist nur die Vorlage).

```bash
D=$(d=$(dirname "$PWD"); while [ "$d" != / ] && [ ! -f "$d/docker/.env" ]; do d=$(dirname "$d"); done; echo "$d/docker"); echo "$D"
grep -E '^(HOST|DEV_URL|STAGE_URL|LIVE_URL)=' "$D/.env" "$D/.env.local" 2>/dev/null
```

Kein Treffer → das Projekt hat kein Boilerplate-Docker; URLs dann per `BASE_URL` übergeben. `STAGE_URL`/`LIVE_URL` mit Platzhalter (`…_WITH_SCHEMA`) → echte URLs mit Schema eintragen, sonst laufen `test:stage`/`test:live` nicht.

`DEV_URL` muss zur `home`-Option der DB passen (sonst CORS-Fehler in allen Tests):

```bash
(cd "$D" && docker-compose exec -T wordpress wp option get home --path=/var/www/html --allow-root)
```

### MailHog und alter Mount

```bash
grep -rnE 'mailhog|mhsendmail|/var/www/html/docker' "$D" --exclude-dir=profiler
```

Treffer → Abschnitt 2, Docker.

### Eigene Mail- und Docker-Anpassungen

```bash
ls "$D" | grep override
ls ../../plugins | grep -i smtp
```

Ein Override oder ein SMTP-Plugin kann Mails an Mailpit vorbei schicken – dann schlägt `mail.spec.mjs` fehl.

## 2. Außerhalb von `utilities/` mitziehen

**Nach dem Kopieren von `utilities/`** (bringt `utilities/tests/`, `utilities/eslint.base.mjs` und die umgestellte `utilities/eslint.config.mjs` mit).

### Theme

1. **`package.json`** von Hand ergänzen (das Migrations-Script übernimmt das nicht):
   ```json
   "devDependencies": {
     "@axe-core/playwright": "^4.13.0",
     "@playwright/test": "^1.64.1",
   ```
   ```json
   "test": "playwright test -c utilities/tests",
   "test:live": "TEST_ENV=live npm test",
   "test:stage": "TEST_ENV=stage npm test",
   "test:visual": "TEST_VISUAL=1 playwright test -c utilities/tests",
   "test:visual:reference": "TEST_ENV=live npm run test:visual -- --update-snapshots=all",
   "test:visual:reference:stage": "TEST_ENV=stage npm run test:visual -- --update-snapshots=all",
   "test:report": "playwright show-report utilities/tests/playwright-report",
   ```
2. **Installieren:** `npm install` **ohne Paketnamen**. Kein Browser-Download nötig, die Tests nutzen das installierte Chrome.
3. **`eslint.config.mjs`** auf die gemeinsame Basis umstellen, nur Projekt-Globals und TypeScript bleiben:
   ```js
   import { defineConfig } from 'eslint/config';
   import tseslint from 'typescript-eslint';
   import base from './utilities/eslint.base.mjs';

   export default defineConfig( [
   	base,
   	{ files: [ '**/*.{js,ts}' ], languageOptions: { globals: { /* project globals */ } } },
   	{ files: [ '**/*.ts' ], extends: [ tseslint.configs.recommended ], rules: { '@typescript-eslint/no-unused-vars': [ 'error', { args: 'none', caughtErrors: 'none' } ] } },
   ] );
   ```
   Optional – die alte Config läuft weiter, bekommt aber keine Regeländerungen aus dem Boilerplate mehr. Danach `npm run lint`.
4. **`js/globals.d.ts`** (nur mit TS, das `wp.i18n` nutzt): `declare const wp: { i18n: typeof import( '@wordpress/i18n' ) };`

### Projekt-`docker/`

Dateien mit dem Boilerplate (`THEME/docker/`) abgleichen, projektspezifische Werte behalten:

| Datei | Änderung |
|---|---|
| `docker-compose.yaml` | Service `mailhog` → `mailpit` (`axllent/mailpit`, Ports 1025/8025); `mysql`: `./:/var/tmp/docker` statt `./:/var/www/html/docker`; `wordpress`: Volume `./profiler:/var/tmp/docker/profiler` |
| `php.ini` | `sendmail_path = "/usr/sbin/sendmail -S mailpit:1025 -t"` |
| `wordpress.dockerfile` | `mhsendmail`-Download entfernen |
| `.bashrc` | `importDB` liest `/var/tmp/docker/db.sql` |
| `xdebug.ini` | `xdebug.output_dir = /var/tmp/docker/profiler` |
| `nginx.conf` | optional: `proxy_cache_path` oben und `proxy_cache …` in `location @live` (Cache für Live-Medien) |
| `.env.tests` | neu, pro Projekt füllen: `TEST_PATHS`, `PRIVACY_ALLOW`, `A11Y_EXCLUDE`, `VISUAL_BLOCK`, `MAIL_FORMS` (Optionen in `utilities/tests/README.md`) |
| `.env.local` | bei HTTP-Auth: `STAGE_AUTH=user:password` (nicht in `.env`, wird committet) |
| `.gitignore` | `cookies.json` |
| `README.md` | Abschnitt MailHog → Mailpit |

Danach Container neu bauen und den alten MailHog-Container entfernen:

```bash
docker-compose build wordpress && docker-compose up -d --remove-orphans
```

### Fixes in Projektdateien (kommen nicht mit, bei Bedarf nachziehen)

| Datei | Änderung im Boilerplate |
|---|---|
| `package.json` | Playwright, axe-core, `test*`-Scripts (siehe oben) |
| `eslint.config.mjs` | Basis aus `utilities/eslint.base.mjs` (siehe oben) |
| `js/globals.d.ts` | `wp.i18n` |
| `docker/*` | siehe Tabelle oben |
| `AGENTS.md` | MailHog → Mailpit |

## 3. Gewollte Verhaltensänderungen

- **Mails** landen in Mailpit statt MailHog, gleiche Oberfläche unter http://localhost:8025. PHP `mail()` geht über busybox `sendmail` (das `mhsendmail`-Binary war nur für amd64).
- **`db.sql`** liegt weiter in `docker/`, ist im MySQL-Container aber unter `/var/tmp/docker/` statt `/var/www/html/docker/` – nur relevant für eigene Import-Befehle.
- **Live-Medien** werden im nginx-Container bis zu 30 Tage gecacht. Geänderte Bilder auf Live: `docker-compose up -d --force-recreate nginx`.
- **`bin/smoke-test.mjs`** bleibt für Projekte vor 2.1; ab 2.1 deckt `npm test` (`smoke.spec.mjs`) dasselbe ab.

## 4. Abnahme nach dem Kopieren

- [ ] `npm run lint` im Theme und in `utilities/` ohne Fehler.
- [ ] `npm test` gegen `DEV_URL` läuft; Funde sind behoben oder bewusst per `.env.tests` ausgenommen (`PRIVACY_ALLOW`, `A11Y_EXCLUDE`, `LINKS_EXCLUDE`).
- [ ] `npm run test:visual:reference` (gegen `live`), danach `npm run test:visual` ohne unerwartete Unterschiede.
- [ ] Formular abschicken (oder `mail.spec.mjs` mit `MAIL_FORMS`): Mail kommt in Mailpit an.
- [ ] `importDB` im MySQL-Container findet `db.sql`.
- [ ] Deploy schließt `utilities/tests/` aus (kommt nicht auf Stage/Live).
