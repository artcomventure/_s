# Upgrade auf 2.3: Neutrale Theme-Namen

Von 2.2.x auf 2.3.0. **Projekte älter als 2.1:** zuerst [`UPGRADE-2.1.md`](UPGRADE-2.1.md) abarbeiten.
Alle Befehle im Projekt-Theme-Ordner (eine Ebene über `utilities/`) ausführen, **vor** dem Kopieren.

Minor-Version, **breaking**: Nur `utilities/` zu kopieren reicht nicht, Abschnitt 2 ist Pflicht. `utilities/` erwartet feste, projektunabhängige Namen statt der pro Projekt umbenannten `_s`-Namen:

- **Stylesheet-Handle `theme-style`:** Pjax/htmx suchen `#theme-style-css` (vorher `#_s-style-css`). Passt das Handle nicht, fehlt beim Seitenwechsel der Anker: neue Block-Styles landen am Ende von `<head>` hinter dem Theme-Stylesheet und überschreiben Theme-CSS. Betrifft nur Projekte mit aktivierter AJAX-Navigation – Projekte, die `_s-style` schon umbenannt hatten, waren bisher schon betroffen.
- **Übersetzungsvorlage `languages/theme.pot`:** `i18n:pot` der Utilities (und von `dev`, `security`, `data-href`, `bodyclass`) zieht `../languages/theme.pot` ab. Fehlt die Datei, bricht `npm run i18n:scan` in `utilities/` ab.
- **Neu:** `bin/rename-theme.sh` benennt ein frisches Boilerplate um (nur für neue Projekte, nicht für bestehende).

## Ablauf (Kurzfassung)

| # | Schritt | Wie |
|---|---|---|
| 0 | Vorbedingungen | Projekt < 2.1 → erst `UPGRADE-2.1.md`. |
| 1 | Prüfen | Skill `utilities-upgrade` bzw. Abschnitt 1 – read-only. |
| 2 | **Kopieren** | `utilities/` komplett aus dem Boilerplate. |
| 3 | Theme | Stylesheet-Handle, `.pot`-Dateien, `package.json` – Abschnitt 2. |
| 4 | Abnahme | Abschnitt 4. |
| 5 | **Kopieren** | `CHANGELOG.md` – erst ganz am Ende, sonst gilt das Projekt schon als 2.3 und die Prüfungen werden übersprungen. |

## 1. Projekt prüfen

### Stylesheet-Handle

```bash
grep -rnE "wp_(enqueue|register)_style\(\s*['\"][^'\"]+['\"]\s*,\s*get_stylesheet_uri\(\)" --include='*.php' --exclude-dir=utilities --exclude-dir=node_modules .
grep -rnE "(pjax|htmx)-theme-style" --include='*.js' --include='*.ts' --include='*.php' --exclude-dir=utilities --exclude-dir=node_modules .
```

Handle `theme-style` → nichts zu tun. Anderes Handle (`_s-style`, `<slug>-style`) → Abschnitt 2. Treffer in der zweiten Zeile (eigener `Alter`-Callback) → der überschreibt den Default; prüfen, ob er nach der Umstellung noch nötig ist.

### Übersetzungsvorlage

```bash
ls languages/*.pot
grep -nE '"i18n:(pot|po)"' package.json
```

`languages/theme.pot` und `theme+.pot` vorhanden und in `package.json` verwendet → nichts zu tun. `_s.pot`/`<slug>.pot` → Abschnitt 2.

## 2. Außerhalb von `utilities/` mitziehen

**Nach dem Kopieren von `utilities/`.**

1. **Stylesheet-Handle** in `functions.php` (bzw. wo das Theme-Stylesheet geladen wird):
   ```php
   wp_enqueue_style( 'theme-style', get_stylesheet_uri() );
   ```
   Hängen Projekt-Styles/-Scripts per Abhängigkeit am alten Handle (`['<slug>-style']`) oder nutzen es in `wp_add_inline_style()`, mit umbenennen:
   ```bash
   grep -rnE "['\"](_s|[a-z0-9-]+)-style['\"]" --include='*.php' --exclude-dir=utilities --exclude-dir=node_modules .
   ```
   Alternative ohne Umbenennen: `Alter.add( 'pjax-theme-style', () => '<slug>-style-css' );` (bzw. `htmx-theme-style`) im Projekt-JS.
2. **`.pot`-Dateien** umbenennen:
   ```bash
   mv languages/<slug>.pot languages/theme.pot
   mv 'languages/<slug>+.pot' 'languages/theme+.pot'
   ```
   In `package.json`:
   ```json
   "i18n:pot": "wp i18n make-pot . languages/theme.pot --merge=languages/theme+.pot --exclude=src,node_modules,**/*.min.*,utilities",
   "i18n:po": "wp i18n update-po languages/theme.pot languages",
   ```
   Text Domain und `.po`/`.mo` bleiben unverändert.
3. **Optional:** Script-Handles `<slug>-app`/`<slug>-navigation` → `theme-app`/`theme-navigation` (nur Konsistenz, nichts hängt daran). Vorher nach Abhängigkeiten suchen (`grep -rn "<slug>-app"`).

### Fixes in Projektdateien (kommen nicht mit, bei Bedarf nachziehen)

| Datei | Änderung im Boilerplate |
|---|---|
| `functions.php` | Handles `theme-style`, `theme-app`, `theme-navigation` |
| `package.json` | `i18n:pot`/`i18n:po` mit `theme.pot` |
| `languages/` | `theme.pot`, `theme+.pot` |
| `README.md` | Abschnitt „Theme“ (Rename-Script, neutrale Namen) |

## 3. Gewollte Verhaltensänderungen

- Theme-Handles und `.pot`-Namen sind ab jetzt in allen Projekten gleich und werden beim Umbenennen nicht mehr angefasst. Nur Text Domain, Funktions-Präfix, `@package` und Theme-Name sind projektspezifisch.
- Im HTML heißt das Theme-Stylesheet `<link id="theme-style-css">` – eigene CSS-/JS-Selektoren auf `#<slug>-style-css` anpassen.

## 4. Abnahme nach dem Kopieren

- [ ] Quelltext einer Seite: `<link … id="theme-style-css">` vorhanden.
- [ ] AJAX-Navigation aktiv: Seitenwechsel auf eine Seite mit anderen Blöcken (z. B. Galerie, Cover) – Block-Styles korrekt, keine Konsolenfehler.
- [ ] `npm run i18n:scan` im Theme und in `utilities/` läuft ohne Fehler.
- [ ] `npm run lint` im Theme ohne Fehler.
