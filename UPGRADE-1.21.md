# Upgrade auf 1.21: `utilities/` in bestehende Projekte übernehmen

Von 1.20.x auf 1.21.0 (Code-Review vom Oktober 2026).
Alle Befehle im Projekt-Theme-Ordner (eine Ebene über `utilities/`) ausführen, **vor** dem Kopieren.

## 1. Projektcode prüfen (kann brechen)

### Events-Modul: `event_list`/`suppress_event_filter` entfernt

Der Block in `posts-list/inc/taxonomy/taxonomy.php` ist gelöscht (Projektwissen im Boilerplate). Nutzt das Projekt ihn, gehört er ins Events-Modul des Projekts.

```bash
grep -rn "event_list\|suppress_event_filter" --include='*.php' . | grep -v node_modules | grep -v '^./utilities/'
```

### `auto_include_files()`: `_`-Dateien werden jetzt wirklich übersprungen

Vorher wurden `_foo.php` trotz Konvention geladen (Bug), jetzt nicht mehr. Außerdem ist die Ladereihenfolge jetzt alphabetisch (vorher je nach Dateisystem). Betrifft auch das Projekt-`inc/`.

```bash
find inc -name '_*' -not -path '*/node_modules/*'
```

Treffer, die geladen werden sollen: umbenennen.

### `Alter.add()`: keine Entfernung gleicher Callbacks mehr

`add()` hat bisher alle Callbacks mit identischem Quelltext entfernt. Jetzt gilt wie bei `addEventListener()` der Vergleich per Referenz. Wird `Alter.add()` mit einer Inline-Funktion **innerhalb eines Behaviours** aufgerufen, kommt bei jedem pjax-Wechsel ein weiterer Callback hinzu.

```bash
grep -rn "Alter.add" --include='*.js' js inc | grep -v '\.min\.js'
```

Treffer innerhalb von `Behaviours.add( … )` → Callback als benannte Funktion außerhalb definieren oder den Aufruf aus dem Behaviour herausziehen.

### `Behaviours` (v2.1.0)

- Prioritäten werden numerisch sortiert (negativ zuerst, Kommazahlen erlaubt). Vorher liefen negative zuletzt → Behaviours mit negativer Priorität laufen jetzt **zuerst**.
- Numerische Gruppennamen (`'1'`, `'-1'`) werden abgelehnt.
- Unkritisch: Ein Fehler in einem Callback stoppt die übrigen nicht mehr; `attach()` mit unbekanntem Namen wirft nicht mehr; `attach( name, settings )` funktioniert jetzt (vorher landeten die Settings als `$context`).

```bash
grep -rnE "Behaviours.add\( *['\"][^'\"]*['\"] *, *[^,]+, *-|Behaviours.add\( *['\"][-0-9.]+['\"]" --include='*.js' js inc | grep -v '\.min\.js'
```

### Contact Form 7: Weiterleitung nach Versand

Die globale Variable `wpcf7MailSentOkRedirects` und das Behaviour `form:wpcf7:mfaromso` gibt es nicht mehr. Das Ziel steht jetzt als `data-redirect` am `<form>`, die Klasse `redirect-on-mail-sent-ok` setzt PHP.

```bash
grep -rn "wpcf7MailSentOkRedirects\|mfaromso" --include='*.js' --include='*.php' . | grep -v node_modules | grep -v '^./utilities/'
```

### Accordion (eigener Block)

`aria-expanded` sitzt jetzt am Header (`role=button`), nicht mehr am Eintrag. Der Inhalt wird über `> [aria-expanded="false"] + .wp-block-accordion-item-content` gesteuert.

```bash
grep -rn "aria-expanded" --include='*.scss' --include='*.css' css js inc | grep -v '\.min\.' | grep -v '\.map'
```

Projekt-CSS, das `aria-expanded` am Eintrag abfragt, anpassen.

Icon: Statt des Text-Pfeils `↓` zeichnet `…-header:not(:last-child):after` jetzt einen Chevron per CSS-Maske (`content: ""`, `background-color: currentColor`, `.75em`). Eigenes Icon per `--accordion-icon: url(…)` (SVG, nur die Form zählt, Farbe kommt aus `color`). Projekte, die den Pfeil per `content` überschreiben, müssen zusätzlich `mask: none; background: none; width: auto; height: auto;` setzen – sonst wird ihr Zeichen von der Chevron-Maske abgedeckt.

```bash
grep -rn "accordion" --include='*.scss' css | grep -i "after\|content"
```

### PHP-Konstanten umbenannt (ohne Aliase)

Alle Pfad-/URL-Konstanten der Module heißen jetzt `UTLTS_<ORDNER>_<UNTERORDNER>_DIRECTORY(_URI)`: Pfad relativ zu `utilities/`, ohne `inc`-Ebenen, Bindestriche entfallen. Beispiele: `UTILITIES_DIRECTORY_URI` → `UTLTS_DIRECTORY_URI`, `ACCORDION_DIRECTORY` → `UTLTS_GUTENBERG_ACCORDION_DIRECTORY`, `LIBS_DIRECTORY_URI` → `UTLTS_JS_LIBS_DIRECTORY_URI`, `CF7_DIRECTORY_URI` → `UTLTS_PLUGINS_CONTACTFORM7_DIRECTORY_URI`. Alte Namen gibt es nicht mehr → unter PHP 8 Fatal Error, falls Projektcode sie nutzt.

```bash
grep -rnoE "\b[A-Z0-9_]+_DIRECTORY(_URI)?\b" --include='*.php' . | grep -v node_modules | grep -v '^./utilities/' | grep -v ':UTLTS_'
```

Treffer, die zu `utilities/` gehören, auf den neuen Namen umstellen (eigene Konstanten des Projekts, z. B. `FONTS_DIRECTORY`, bleiben).

### Globale JS-Funktionen → `utlts`

`getBackgroundColor`, `isInternalUrl` u. a. liegen jetzt unter `window.utlts`. Die alten globalen Namen bleiben als Aliase erhalten, es ist nichts zu tun. Neuer Code sollte `utlts.…` verwenden.

### Scripts laden mit `defer`

Alle Frontend-Scripts aus `utilities/` (inkl. registrierter Libs wie `gsap`, `tablesort`, `tom-select`) haben jetzt `'strategy' => 'defer'`. Editor- und reine Admin-Scripts sind unverändert.

- **Kann brechen:** Inline-`<script>` in Templates/Inhalten, die sofort auf `Behaviours`, `Alter`, `utlts`, `BREAKPOINTS`, `gsap` o. Ä. zugreifen → läuft jetzt vor der Library. Code in `document.addEventListener( 'DOMContentLoaded', … )` packen oder in eine Script-Datei mit Abhängigkeit verschieben.
- **Wirkt sonst nicht:** Hängt ein Projekt-Script ohne Strategie von einem Utilities-Script ab (z. B. `functions.php` → `behaviours`), setzt WordPress die ganze Kette stillschweigend auf blockierend zurück. Projekt-Scripts ebenfalls mit `['in_footer' => true, 'strategy' => 'defer']` einbinden (Boilerplate `functions.php` ist angepasst) und fehlende Abhängigkeiten ergänzen (z. B. `css-breakpoints`, wenn `BREAKPOINTS` genutzt wird).
- **Prüf-Script** (nach dem Kopieren von `utilities/`, im WordPress-Container): `wp eval-file wp-content/themes/THEME/utilities/bin/check-defer.php --allow-root`. Meldet fehlende Abhängigkeiten, blockierende Projekt-Scripts und Inline-Scripts (Theme-PHP, Beiträge), die Utilities-Globals nutzen. Heuristik: Nutzung in `Behaviours.add()`-/`DOMContentLoaded`-Callbacks ist unkritisch, wird aber mitgemeldet.
- **Prüfen:** Im Seitenquelltext ohne `defer` = zurückgestuft: `curl -s URL | grep -o '<script[^>]*src=[^>]*>' | grep -v ' defer '`. Danach wichtige Seiten mit offener Konsole durchklicken.

## 2. Außerhalb von `utilities/` mitziehen

- **BrowserSync:** Port **3456** (UI 3457) in der Projekt-`package.json` (`--port 3456 --ui-port 3457`). Sonst bindet `inc/dev/inc/browser-sync.php` das Script nicht ein. Alternativ den Port per Filter `browser_sync_port` setzen.
- **Server:** `.wasm` muss als `application/wasm` ausgeliefert werden (dotlottie). nginx:
  ```nginx
  location ~* \.wasm$ { types { application/wasm wasm; } expires 30d; }
  ```
  Prüfen: `curl -sI https://DOMAIN/wp-content/themes/THEME/utilities/js/libs/dotlottie-wc/dotlottie-player.wasm | grep -i content-type`
- **Server: DB-Dumps sperren.** `db-port` legt Dumps unter `uploads/sqldumps/` ab (zufälliger Name, `.htaccess` + `index.php` werden angelegt). Apache greift damit, nginx braucht eine eigene Regel, sonst sind die Dumps per URL erreichbar:
  ```nginx
  location ^~ /wp-content/uploads/sqldumps/ { return 404; }
  ```
  Alte Dumps mit erratbarem Namen (aus der Zeit vor dem Token) löschen.
- **SCSS-Partials** aus `utilities/css/` wirken erst nach einem Neubau der Projekt-CSS (`npm run build`), u. a.:
  - `mixins/_sr-only.scss`: veraltetes `clip` entfernt → Skip-Link wird bei Fokus endlich sichtbar.
  - `mixins/_pseudons.scss` (`icon()`): `content: $icon / ""` → Icon-Glyphen werden von Screenreadern nicht mehr vorgelesen.
  - `_misc.scss`: `[video]`-Shortcode flexibel (`.wp-video video { width: 100%; height: auto }`). Ohne Neubau bleibt das Video auf Mobilgeräten breiter als die Spalte.

### Fixes in Projektdateien (kommen nicht mit, bei Bedarf nachziehen)

Diese Dateien liegen auf der ersten Ebene und werden beim Kopieren von `utilities/` nicht aktualisiert. Im Boilerplate sind sie gefixt, in laufenden Projekten bestehen die Fehler weiter, sofern dort nicht anders gelöst. Abgleich am einfachsten per Diff gegen den Boilerplate (`git diff` bzw. Dateivergleich).

| Datei | Fehler | Fix im Boilerplate |
|---|---|---|
| `js/navigation.js` (+ `.min.js`) | Klick-Listener mit Parameter `event`, im Code `e.target` → `ReferenceError`, Menü schließt bei Klick außerhalb/Anker nie | Parameter und Verwendung vereinheitlicht (`e`) |
| `js/navigation.js` | Escape schließt das Menü nicht | `keydown` auf `document`: schließt und fokussiert `.menu-toggle` |
| `js/navigation.js` | `touchstart` mit `preventDefault` → Elternlinks per Touch nicht erreichbar | `toggleFocus()` samt Listenern entfernt (Dropdowns projektspezifisch, z. B. `:focus-within`) |
| `inc/template-tags.php`, `template-parts/content.php`, `content-page.php` | `the_permalink()` gibt direkt aus → URL steht vor `<figure>`, `data-href` leer | `esc_url( get_permalink() )`; Bildunterschriften per `wp_kses_post` |
| `js/app.js` (+ `.min.js`) | Swiper: `ResizeObserver` pro Slider nie abgemeldet (Leak über pjax-Wechsel) | fluid `spaceBetween` über `swiper.on( 'resize', … )` |
| `js/app.js` | Scroll-Handler liest `offsetHeight` und schreibt `#masthead`-Klassen bei jedem Scroll | Klassen nur bei Änderung, ohne erzwungene Layout-Berechnung |
| `functions.php` | `load_theme_textdomain( wp_get_theme()->get( 'TextDomain' ) )` (falsch bei umbenanntem Theme) | `load_theme_textdomain( '_s', … )` |
| `theme.json` | h3 `18.27px` (Zahlendreher), Slug `White` | `18.72px`, `white` (Achtung: gespeichertes `"textColor":"White"` erkennt der Farbwähler danach nicht mehr, die CSS-Klasse bleibt `has-white-color`) |
| `package.json` | BrowserSync auf Port 3000 | `--port 3456 --ui-port 3457` (siehe oben) |

## 3. Gewollte Verhaltensänderungen (Kunden informieren)

- **Redakteure** (Rolle Editor) haben kein `manage_options` mehr: keine Maps-Einstellungen, keine 404-Seite, eingeschränkter Customizer (Website-Informationen, Seiten für …, Menüs, Widgets).
- **SVG-Upload** nur noch mit `unfiltered_html` (Autoren nicht, Redakteure auf Einzelseiten schon).
- **404:** Home, Archive und Suche bekommen ohne Option `error404` kein `noindex` mehr (vorher Bug).
- **`srcset`** ist wieder aktiv (besser für mobile Ladezeiten).
- **Ohne pjax** lädt nur noch das genutzte Block-CSS statt der ganzen Block-Bibliothek.
- **pjax:** Nach einem Seitenwechsel bekommt die `h1` den Fokus, bei Hash-Links das Ziel.
- **`mailto:`/`tel:`-Links** öffnen nicht mehr in einem neuen Tab.
- **Videos:** YouTube/Vimeo-Erkennung ohne HTTP-Anfrage pro Render. `[video]` ohne `width`/`height` ist nicht mehr fest 640 × 360, sondern füllt die Inhaltsspalte (Maße aus dem Anhang bzw. `$content_width`); ausdrückliche Angaben im Shortcode gelten weiter.

## 4. Abnahme nach dem Kopieren

- [ ] Startseite, eine Inhaltsseite, ein Archiv, Suche und 404 laden ohne PHP-Warnungen (`wp-content/debug.log`) und ohne JS-Fehler (Konsole).
- [ ] Optischer Vergleich vorher/nachher, besonders ohne pjax (Reihenfolge des Block-CSS) und Fokus-Rahmen an der `h1` nach pjax-Wechsel.
- [ ] pjax-Navigation (falls aktiv): mehrere Wechsel, Zurück/Vorwärts, Hash-Link auf andere Seite.
- [ ] Accordion: Auf-/Zuklappen per Klick, Enter und Leertaste; Darstellung (Pfeil, offener Zustand).
- [ ] Contact Form 7: Formular mit URL als Erfolgsmeldung leitet weiter, eines ohne URL zeigt die Meldung.
- [ ] Videos: YouTube/Vimeo mit Cookie-Wall, selbst gehostetes Video als Player.
- [ ] dotlottie-Animationen laufen (Netzwerk-Tab: `.wasm` mit Status 200 und `application/wasm`).
- [ ] Als Redakteur einloggen: Dashboard, Seiten bearbeiten, Customizer und Menüs funktionieren.
- [ ] Backend: Beitragsliste, Term-Liste (falls Term-Farben), Login-Seite mit Logo.
