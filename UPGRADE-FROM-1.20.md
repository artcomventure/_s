# Kurzweg: Projekte vor 1.21 direkt auf 2.1

Für Projekte mit Utilities < 1.21.0. Ersetzt das schrittweise Abarbeiten von [`UPGRADE-1.21.md`](UPGRADE-1.21.md), [`UPGRADE-2.0.md`](UPGRADE-2.0.md) und [`UPGRADE-2.1.md`](UPGRADE-2.1.md) – dort stehen Begründungen und Details.
Alle Befehle im Projekt-Theme-Ordner (eine Ebene über `utilities/`). `BP` = `~/Develop/wordpress/wp-content/themes/_s`. Voraussetzung: Node 24 (`nvm install 24`), Google Chrome.

## 1. Vorher prüfen (ändert nichts)

- Skill `utilities-upgrade` bzw. Abschnitt 1 aller drei Anleitungen. Treffer notieren, behoben werden sie in Schritt 3.
- Projektanpassungen in `utilities/` (gegen die Konvention) finden: `git log --oneline -- utilities/` – Commits außer Utilities-Syncs prüfen und die Änderungen nach `css/`, `js/` bzw. `inc/` verschieben, sonst gehen sie beim Kopieren verloren.

## 2. Kopieren

| Was | Achtung |
|---|---|
| `utilities/` | komplett (inkl. `css/`) |
| `BP/docker/` → Projekt-`docker/` | **behalten:** `.env`, `.env.local`, `db.sql`. Neue Schlüssel aus `BP/docker/.env` in `.env` ergänzen, `.env.tests` fürs Projekt füllen. |
| `.browserslistrc`, `.npmrc`, `.nvmrc`, `stylelint.config.mjs`, `tsconfig.json` | – |
| `eslint.config.mjs`, `js/globals.d.ts` | danach Projekt-Globals ergänzen |
| `package.json` | danach `name`, `description`, `dependencies` und eigene Scripts aus der alten Datei zurückholen |
| `js/navigation.js`, `js/app.js` (+ `.min.js`), `inc/template-tags.php`, `template-parts/content.php`, `template-parts/content-page.php` | nur wenn im Projekt unverändert, sonst Fixes per Diff übernehmen |
| `CHANGELOG.md` | **erst ganz am Ende**, sonst gilt das Projekt als aktuell und die Prüfungen werden übersprungen |

## 3. Von Hand

1. **Weitere `package.json`** (z. B. `media/fonts/Icont/`, Projekt-Module) umstellen: `node BP/bin/migrate-build.mjs .` (Dry Run), dann mit `--write`.
2. **Installieren:** `npm install` ohne Paketnamen. Im Projekt-`docker/`: `docker-compose build wordpress && docker-compose up -d --remove-orphans`.
3. **Treffer aus Schritt 1** beheben (u. a. umbenannte `UTLTS_…`-Konstanten, `Alter.add()` in Behaviours, CF7-Weiterleitung, Accordion-CSS).
4. **`css/_variables.scss`:** Icont-Zeile → `"\"\\f1#{dec2hex(…)}\""`, sonst sind alle Icons kaputt.
5. **Projekt-SCSS migrieren:**
   ```bash
   for m in division color if-function calc-interpolation strict-unary; do npx sass-migrator@2 $m --migrate-deps $(find css -name '[!_]*.scss'); done
   npx sass-migrator@2 module --built-in-only --migrate-deps $(find css -name '[!_]*.scss')
   ```
   Danach `math.div(var(` → `/` zurück, `@use "sass:…"` an den Dateianfang.
6. **`functions.php`:** Projekt-Scripts mit `['in_footer' => true, 'strategy' => 'defer']` einbinden, `load_theme_textdomain( '_s', … )`. **`theme.json`:** h3 `18.72px`, Slug `white`.
7. **Lint und Build:** `npm run lint` und `npm run build` im Theme, in `utilities/` und in Projekt-Modulen.
8. **Server (Stage/Live, nginx):** `.wasm` als `application/wasm` ausliefern und `/wp-content/uploads/sqldumps/` sperren (Regeln in `UPGRADE-1.21.md`, Abschnitt 2).
9. **`AGENTS.md`** des Projekts: Abschnitt Build aus dem Boilerplate übernehmen, MailHog → Mailpit.

## 4. Abnahme

- [ ] `npm test` gegen `DEV_URL` ohne offene Funde; `npm run test:visual:reference`, danach `npm run test:visual`.
- [ ] `BP/bin/check-defer.php` ohne offene Treffer (Aufruf in `UPGRADE-1.21.md`).
- [ ] Checklisten aus Abschnitt 4 der drei Anleitungen.
- [ ] Kunden über die Verhaltensänderungen informieren (`UPGRADE-1.21.md`, Abschnitt 3).
