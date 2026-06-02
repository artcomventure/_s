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
```

### Utilities (eigener Build-Prozess)

```bash
# in _s/utilities/
npm install
npm run watch   # Alle Utilities-Module im watch mode
npm run build   # Alle Utilities-Module für Produktion
```

Der Utilities-Build umfasst u.a. eigene Sub-Module:
`accessibility`, `gutenberg`, `dev`, `video`, `security`, `post-edit-link` sowie JS-Libs (`air-datepicker`, `autosize`, `custom-select`, `pjax`, `dotlottie-wc`)

> Gulp ist eine Altlast und wird nicht mehr aktiv verwendet.

## i18n / Übersetzungen

```bash
# Projektspezifische Übersetzungen (in _s/)
npm run theme:i18n:scan    # .pot und .po aktualisieren
npm run theme:i18n:create  # .mo und .json generieren

# Utilities-Übersetzungen (in _s/utilities/)
npm run i18n:scan
npm run i18n:create
```

## Konventionen

- Comments and variable names always in **English**
- Comments as short as possible
- **Projektspezifische Änderungen** immer in den Ordnern der ersten Ebene – nie direkt in `utilities/`
- `utilities/` wird als eigenständige Einheit gepflegt und über Projekte hinweg synchronisiert
- Kompilierte Assets (`style.css`, `*.min.js`) werden **committet**
- `node_modules/` wird **nicht** committet
- BrowserSync proxied `http://localhost` – der Docker-Container muss laufen