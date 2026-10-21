# Upgrade auf 2.0: Build-Umstellung (esbuild, Dart Sass, ESLint, Stylelint, TypeScript)

Von 1.21.x auf 2.0.0. **Projekte älter als 1.21:** zuerst [`UPGRADE-1.21.md`](UPGRADE-1.21.md) abarbeiten.
Alle Befehle im Projekt-Theme-Ordner (eine Ebene über `utilities/`) ausführen, **vor** dem Kopieren.

Major-Version, weil `utilities/` allein nicht reicht: Die Module bauen per `utilities/bin/build-js.mjs` und `utilities/bin/build-css.mjs` und brauchen esbuild und Dart Sass in den `node_modules` des Themes; das Projekt braucht Node 24, neue Config-Dateien und angepasstes SCSS.

- **JS:** Babel → esbuild (`NAME.js`/`NAME.ts` → `NAME.min.js`, es2017), ESLint, TypeScript-Typprüfung.
- **SCSS:** node-sass → Dart Sass (`sass`) mit Autoprefixer, Stylelint. `@import` bleibt vorerst (Umstieg auf `@use` ist ein eigenes, späteres Major), alle anderen Deprecations sind behoben.
- **Node 24** statt 20 (node-sass war der einzige Grund für Node 20).

## 1. Projektcode prüfen (kann brechen)

### Node 24

```bash
ls -d ~/.nvm/versions/node/v24.* 2>/dev/null
```

Kein `v24.x` → `nvm install 24`. Das Projekt bekommt eine `.nvmrc` (`nvm use` im Theme-Ordner).

### Babel- und node-sass-Aufrufe außerhalb von `utilities/`

Das Migrations-Script (Abschnitt 2) stellt `babel SRC -d DIR …`/`babel SRC -o SRC.min.js …` und `node-sass SRC -o DIR …`/`node-sass SRC > FILE …` um. Andere Formen meldet es als „Manual“.

```bash
grep -rnE '"[^"]*(babel|node-sass|postcss) ' --include=package.json . | grep -v node_modules | grep -v '^./utilities/'
find . \( -name '.babelrc*' -o -name 'babel.config.*' -o -name 'postcss.config.*' -o -name '.postcssrc*' \) -not -path '*/node_modules/*'
```

Eigene Babel-Configs gelten nicht mehr (esbuild ohne Plugins, es2017). postcss/autoprefixer waren im Boilerplate installiert, aber nie aufgerufen; jetzt laufen sie in `build-css.mjs`. Eigene postcss-Aufrufe (`postcss-cli`) also prüfen und meist entfernen – Autoprefixer nicht doppelt laufen lassen.

### JS: `const`/`let`/`class` auf oberster Ebene

Babel hat nach ES5 transpiliert, also wurde aus `const foo` auf oberster Ebene ein `var foo`. Jetzt bleibt es `const`. Klassische Scripts teilen sich den globalen Scope:

- Zwei Scripts mit demselben Namen auf oberster Ebene → `SyntaxError: Identifier 'foo' has already been declared`, das zweite Script läuft gar nicht.
- `window.foo` ist bei `const`/`let`/`class` `undefined` (bei `var` war es gesetzt).

```bash
grep -rnE '^(const|let|class) +[A-Za-z_$][A-Za-z0-9_$]*' --include='*.js' --exclude='*.min.js' js inc | grep -v node_modules
```

Treffer in Scripts, die über `build-js.mjs` gebaut werden (nicht `wp-scripts`): in eine IIFE packen (`(function() { … })();`, wie `js/navigation.js`) oder, falls bewusst global, als `window.foo = …` schreiben.

### SCSS: Escapes in Interpolation (Icont-Icons!)

Dart Sass wertet `\f1` innerhalb von `#{…}` als Escape aus: Die Boilerplate-Zeile in `css/_variables.scss` erzeugte damit `content: "ñ01"` statt `"\f101"` → **alle Icont-Icons kaputt**.

```bash
grep -rn '#{\\' --include='*.scss' css inc | grep -v node_modules
```

Treffer wie `"\"#{\f1+dec2hex(…)}\""` umstellen auf `"\"\\f1#{dec2hex(…)}\""` (Backslash verdoppeln, außerhalb der Interpolation). Nach dem Build prüfen: `grep -o 'content:"[^"]*"' style.css | head` zeigt die Icon-Codes.

### SCSS: `~`-Imports

`@import "~paket/…"` (webpack-Stil) kennt Dart Sass nicht.

```bash
grep -rnE "@import ['\"]~" --include='*.scss' css inc | grep -v node_modules
```

Treffer auf relativen Pfad zu `node_modules` umstellen (wie `utilities/css/_normalize.scss`).

### SCSS: `@extend` erzeugt andere Selektoren

Dart Sass lässt bei `@extend` Selektoren weg, die ein anderer erzeugter Selektor abdeckt (libsass/node-sass nicht). Dadurch kann die Spezifität sinken. Im Boilerplate betraf das nur das Button-Farbsystem in `css/_typography.scss` (siehe Abschnitt 3) und war dort eine Korrektur. Projekte mit vielen eigenen `@extend` nach dem Build optisch prüfen (Abschnitt 4).

```bash
grep -rc '@extend' --include='*.scss' css inc | grep -v ':0$'
```

## 2. Außerhalb von `utilities/` mitziehen

**Nach dem Kopieren von `utilities/`** (bringt `utilities/bin/build-js.mjs`, `utilities/bin/build-css.mjs`, `utilities/eslint.config.mjs`, `utilities/stylelint.config.mjs`, die umgestellten Modul-`package.json`s und das migrierte Utilities-SCSS mit).

1. **Migrations-Script** (aus dem Boilerplate, kommt nicht ins Projekt). Erst ohne `--write` (zeigt nur an), dann mit:
   ```bash
   node ~/Develop/wordpress/wp-content/themes/_s/bin/migrate-build.mjs . [--write]
   ```
   - Legt fehlende Config-Dateien aus dem Boilerplate an: `eslint.config.mjs`, `stylelint.config.mjs`, `tsconfig.json`, `js/globals.d.ts`, `.npmrc`, `.nvmrc`, `.browserslistrc` (nicht, wenn die `package.json` schon `browserslist` hat; und die beiden in `utilities/`, falls sie fehlen). Vorhandene werden nie überschrieben (`Kept (differs from boilerplate)` → per Diff abgleichen).
   - Stellt Babel- und node-sass-Aufrufe in allen `package.json` um, ergänzt `lint`/`lint:*`/`prebuild` (Theme und `utilities/`) und die devDependencies (`esbuild`, `eslint`, `@eslint/js`, `globals`, `typescript`, `typescript-eslint`, `sass`, `postcss`, `autoprefixer`, `stylelint`, `stylelint-config-recommended-scss`) und `engines` (Node ≥ 24); Babel, node-sass und ungenutztes `postcss-cli` fliegen raus. Formatierung (Tabs, Leerzeilen) bleibt.
   - `Warnings`: Dateien, die Babel rekursiv mitgebaut hat und die jetzt nichts mehr baut. `Manual`: Aufrufe, die von Hand umzustellen sind (Exit-Code 2).
2. **Installieren** mit Node 24: `npm install` **ohne Paketnamen** (mit Paketnamen schreibt npm die `package.json` neu und entfernt Leerzeilen). `ERESOLVE` → `.npmrc` fehlt (`legacy-peer-deps`, nötig solange `@wordpress/scripts` 26 ESLint 8 verlangt).
3. **Projekt-SCSS migrieren** (Utilities-SCSS ist schon migriert):
   ```bash
   for m in division color if-function calc-interpolation strict-unary; do npx sass-migrator@2 $m --migrate-deps $(find css -name '[!_]*.scss'); done
   npx sass-migrator@2 module --built-in-only --migrate-deps $(find css -name '[!_]*.scss')
   ```
   Danach prüfen:
   - `grep -rn 'math\.div(var(' --include='*.scss' css` – der `division`-Migrator macht aus CSS-Schrägstrichen (`alpha(from var(--a) / var(--b))`) fälschlich `math.div(…)`. Zurück auf `/` (erzeugt keine Warnung).
   - `@use "sass:…"`-Zeilen gesammelt an den Dateianfang (der Migrator verteilt sie teils um Doc-Kommentare).
   - `npm run build` zeigt keine Deprecation-Warnungen mehr (`@import` ist stummgeschaltet).
4. **Lint:** `npm run lint` im Theme und in `utilities/` (ESLint, `tsc`, Stylelint). `prebuild` startet Lint vor jedem `npm run build` – mit Fehlern baut nichts mehr.
   - Unbekannte Globals des Projekts (`wp_localize_script`-Daten, Libraries) in `eslint.config.mjs` unter `globals` eintragen, für TS zusätzlich in `js/globals.d.ts`. Die kopierten Dateien enthalten die Boilerplate-Globals `CCM` und `doSliders` – nicht genutzte entfernen.
   - Regeln nicht abschalten, sondern Code korrigieren. Erlaubt sind bereits: ungenutzte Parameter, stille `catch`-Blöcke, leere SCSS-Regeln (Platzhalter), Sass-`@import` an beliebiger Stelle.
   - Typische Stylelint-Funde: `word-wrap` → `overflow-wrap`, `grid-gap` → `gap`, `clip: rect(…)` → `clip-path: inset(50%)`, `font-smoothing` (ohne Prefix, wirkungslos) entfernen, fehlende Leerzeichen um Operatoren.
5. **Bauen:** `npm run build` im Theme und in `utilities/`, Projekt-Module per eigenem `build`. Module, deren `build` `wp-scripts … &` im Hintergrund startet, schreiben `build/`-Dateien mit Minifier-Rauschen neu – unbeabsichtigte `build/`-Änderungen zurücksetzen.

Mit Claude Code: Skill `utilities-upgrade` führt Abschnitt 1 aus und bietet danach an, Abschnitt 2 umzusetzen.

### Fixes in Projektdateien (kommen nicht mit, bei Bedarf nachziehen)

| Datei | Änderung im Boilerplate |
|---|---|
| `package.json` | Babel/node-sass → `build-js.mjs`/`build-css.mjs`, `lint`/`prebuild` (macht das Migrations-Script) |
| `css/_variables.scss` | Icont-Codes: `"\"\\f1#{dec2hex(…)}\""` (siehe Abschnitt 1) |
| `css/_typography.scss` | `word-wrap` → `overflow-wrap` |
| `css/*.scss` | Migratoren: `map.get`, `math.div`, `color.adjust` u. a. mit `@use "sass:…"` |
| `media/fonts/Icont/package.json` | `node-sass ./Icont.scss > ./font.css` → `build-css.mjs Icont.scss font.css` (macht das Migrations-Script) |
| `js/navigation.js` (+ `.min.js`) | ungenutzte Variable `$header` entfernt (Lint) |
| `js/app.js` (+ `.min.js`) | `slidesPerView = ++i` → `i + 1` (Lint) |
| `.browserslistrc` | Autoprefixer-Ziele: `defaults`, `not op_mini all` |
| `AGENTS.md` | Abschnitt Build: esbuild, Dart Sass, Autoprefixer, `npm run lint`, Node 24, `.npmrc` |

## 3. Gewollte Verhaltensänderungen

- **Buttons mit Stil „Fill“ und Hintergrundfarbe:** bei `:hover`/`.active` jetzt Text in der Button-Farbe (wie ohne ausdrücklich gewählten Stil), vorher schwarz – node-sass hatte die Absicht im Code (`@extend %color-…`) wegen der Spezifität nicht umgesetzt.
- **CSS-Ausgabe** von Dart Sass: `calc()` wird vereinfacht, `@extend` erzeugt weniger (redundante) Selektoren, Deklarationen nach verschachtelten Regeln landen in einem eigenen Block danach. Im Browser geprüft gleichwertig (bis auf den Button-Punkt). `style.css` ist ~4 % kleiner.
- **Autoprefixer:** ergänzt Vendor-Präfixe nach `.browserslistrc` (im Boilerplate z. B. `-webkit-appearance`, `-webkit-mask`, `-webkit-text-decoration`). Handgeschriebene Präfixe können weg, Stylelint meldet sie aber nicht.
- **Browser-Ziel es2017** statt ES5: kleinere `.min.js`, kein Internet Explorer mehr. Nur relevant, wenn ein Kunde IE11 vertraglich zugesichert hat.
- **TypeScript:** `NAME.ts` wird zu `NAME.min.js` (Typprüfung per `npm run lint`). Es gibt keine `NAME.js`, deshalb `NAME.min.js` direkt einbinden – der Minify-Filter tauscht nur vorhandene `.js` aus:
  ```php
  wp_enqueue_script( 'PREFIX-example', get_template_directory_uri() . '/js/example.min.js', ['behaviours'], false, ['in_footer' => true, 'strategy' => 'defer'] );
  ```
  Beispiel: `js/example.ts`.
- **`npm run build` lintet vorher** und bricht bei Fehlern ab.
- **Watch-Modus** (`npm run watch`): CSS mit Source Maps und 2 Leerzeichen Einrückung (Dart Sass kennt keine Tabs); neue JS-Dateien erst nach einem Neustart.
- **Editor-Script „Beitragsbilder“** (`utilities/inc/featured-images/`) ist 3,6 statt 133 KB groß (versehentlich mitgebündeltes CSSLint entfernt).

## 4. Abnahme nach dem Kopieren

- [ ] `npm run lint` im Theme und in `utilities/` ohne Fehler.
- [ ] `npm run build` im Theme und in `utilities/` läuft ohne Deprecation-Warnungen durch; `git status` zeigt nur erwartete Änderungen (`.min.js`, `.css`, umgestellte `package.json`, neue Config-Dateien, migriertes SCSS).
- [ ] Frontend mit offener Konsole: Startseite, Inhaltsseite, Archiv, Suche, 404 ohne JS-Fehler (`bin/smoke-test.mjs`), besonders `SyntaxError: … already been declared`.
- [ ] Optischer Vergleich vorher/nachher: Buttons (Stile „Fill“/„Outline“, Farben, Hover), Formulare, Überschriften, Icons (Icont, Dashicons).
- [ ] pjax-Navigation (falls aktiv), Cookie-Wall, Videos (video.js), Google Maps.
- [ ] Editor: Beitragsbilder-Panel, Accordion, Seitenverhältnis/Fokuspunkt, Ausblenden-Optionen, `data-href`-Links, Posts-List-Block, dotlottie-Block, Editor-Styles.
- [ ] `npm run watch`: Änderung an einer `js/*.js` und an einem Partial in `utilities/css/` landet in `.min.js` bzw. `style.css`.
