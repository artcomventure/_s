# _s – WordPress Theme Boilerplate

Underscores-Fork von **artcom venture GmbH** als Basis für Kundenprojekte.

## Struktur

Alle Ordner und Dateien auf **erster Ebene** (z.B. `css/`, `js/`, `inc/`, `functions.php`) sind **projektspezifisch** und werden pro Projekt angepasst.

Die einzige Ausnahme ist `utilities/` – der projektübergreifende Kern des Boilerplates.

```
_s/
├── css/                 # Projektspezifisches SCSS
├── js/                  # Projektspezifisches JS
├── inc/                 # Projektspezifische PHP-Includes
├── languages/           # Projektspezifische Übersetzungen
├── functions.php        # Projektspezifisch
├── ...                  # Alle weiteren Dateien: projektspezifisch
│
└── utilities/           # ⬅ Boilerplate-Kern, projektübergreifend
    ├── utilities.php        # Einstiegspunkt (wird in functions.php eingebunden)
    ├── auto-include-files.php
    ├── css/                 # Gemeinsame SCSS-Partials (base, normalize, mixins, functions)
    ├── js/                  # Gemeinsame JS-Utilities (helpers, behaviours, BREAKPOINTS, …)
    ├── inc/                 # Wiederverwendbare PHP-Module (s.u.)
    └── languages/           # Übersetzungen der Utilities
```

### `utilities/inc/` – PHP-Module (Auswahl)

| Modul | Funktion |
|-------|----------|
| `accessibility/` | Barrierefreiheit-Helpers |
| `gutenberg/` | Gutenberg-Erweiterungen |
| `security/` | Sicherheits-Hardening |
| `dev/` | Debug- und Entwicklungshelfer |
| `video/` | Video-Einbindung (video.js) |
| `cookie-wall/` | Cookie-Consent |
| `shortcodes/` | Wiederverwendbare Shortcodes |
| `gmaps/` | Google Maps Integration |
| `performance/` | Performance-Optimierungen |
| `rest-api/` | REST-API-Erweiterungen |

## Build (Node.js lokal auf dem Mac ausführen)

### Projektspezifisches Theme

```bash
# in _s/
npm install
npm run watch   # SCSS + JS (watch mode) + BrowserSync
npm run build   # SCSS + JS für Produktion (minifiziert)
npm run lint    # ESLint (js/) + Typprüfung (tsc --noEmit, js/**/*.ts) + Stylelint (css/)
```

- JS/TS: `NAME.js` bzw. `NAME.ts` → `NAME.min.js` via esbuild (`utilities/bin/build-js.mjs`, Target es2017, kein Bundling).
- SCSS: Dart Sass + Autoprefixer (Ziele in `.browserslistrc`) via `utilities/bin/build-css.mjs` (Build komprimiert, Watch mit Source Maps). `@import` bleibt bis zur `@use`-Umstellung (Warnung stummgeschaltet); neue Built-in-Aufrufe als Modul (`map.get`, `math.div`, `@use "sass:…"`). `sass` ist exakt gepinnt (1.106.0 stürzt bei `[attr=""]` ab).
- Node **24** (`.nvmrc`, `engines`). `.npmrc` setzt `legacy-peer-deps` (wp-scripts 26 will ESLint 8).

### Utilities (eigener Build-Prozess)

```bash
# in _s/utilities/
npm install
npm run watch   # utilities/js + css (watch mode)
npm run build   # utilities/js + css (vorher Lint aller Utilities)
npm run lint    # ESLint + Stylelint für ganz utilities/ (eigene Configs, Vendor-Code ausgenommen)
```

Der Utilities-Build umfasst u.a. eigene Sub-Module:
`accessibility`, `gutenberg`, `dev`, `video`, `security`, `post-edit-link` sowie JS-Libs (`air-datepicker`, `autosize`, `custom-select`, `pjax`, `dotlottie-wc`)

**Nur das betroffene Modul bauen:** Hat der Unterordner unter `utilities/` eine eigene `package.json` mit Build-Script, ausschließlich diese nutzen (`cd utilities/inc/<modul> && npm run build`). Der Build in `utilities/` selbst baut nur `utilities/js/*.js`, `utilities/css/` und Übersetzungen – keine Module.

> Gulp ist eine Altlast und wird nicht mehr aktiv verwendet.

## i18n / Übersetzungen

```bash
# Projektspezifische Übersetzungen (in _s/)
npm run i18n:scan    # .pot und .po aktualisieren
npm run i18n:create  # .mo und .json generieren

# Utilities-Übersetzungen (in _s/utilities/)
npm run i18n:scan
npm run i18n:create
```

## Konventionen

- Comments and variable names always in **English**
- Comments as short as possible
- **Projektspezifische Änderungen** immer in den Ordnern der ersten Ebene – nie direkt in `utilities/`
- `utilities/` wird als eigenständige Einheit gepflegt und über Projekte hinweg synchronisiert
- Utilities-Verhalten im Projekt anpassen: über Hooks und Filter (`apply_filters`/`do_action`, JS: `Alter`), **nicht** durch Überschreiben von Funktionen – Projekt-`inc/` lädt erst nach `utilities/`. Fehlt ein Eingriffspunkt, in `utilities/` gezielt einen Filter ergänzen oder die anonyme Funktion benennen (damit `remove_action()`/`remove_filter()` greift). Ausnahme: Closures sind ok, wenn das Feature per Filter abschaltbar und anpassbar ist (z. B. `utilities/inc/site-icon.php`)
- Namespaces/Klassen (`Utlts\`): geplant, Konvention in `TODO.md` – bis zur Einführung des Autoloaders prozedural bleiben
- Pfad-/URL-Konstanten in `utilities/`: `UTLTS_<ORDNER>_<UNTERORDNER>_DIRECTORY` / `…_DIRECTORY_URI` (Pfad relativ zu `utilities/`, ohne `inc`-Ebenen, Bindestriche entfallen; z. B. `inc/gutenberg/inc/accordion` → `UTLTS_GUTENBERG_ACCORDION_DIRECTORY`)
- Neue Optionen, Theme-Mods und Meta-Keys mit Präfix: `utlts_` in `utilities/`, Projekt-Präfix in den Ordnern der ersten Ebene (bestehende Namen wie `slugs`, `pjax`, `gmaps` bleiben unverändert)
- Kompilierte Assets (`style.css`, `*.min.js`) werden **committet**
- `node_modules/` wird **nicht** committet
- BrowserSync proxied `http://localhost` auf Port **3456** (UI 3457, nicht 3000/3001 wegen Kollisionen) – der Docker-Container muss laufen. Port in `package.json` (`--port`) und per Filter `browser_sync_port` (`utilities/inc/dev/inc/browser-sync.php`) gleich halten

## Git & Workflow

- **NEVER commit or push without explicit user consent**: Do NOT run `git commit`, `git push`, or create any commits automatically, even during plan execution or subagent runs. Always leave changes uncommitted in the working tree for the user to review.
