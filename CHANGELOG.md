# Changelog

## 1.10.0 - 2024-08-06
**Added**

* `[data-href]` Gutenberg input (in `THEME/utilities/gutenberg/inc/data-href/`).
* success/error default colors (in `THEME/css/_variables.scss`).
* Don't _Pjax_ zip files (in `THEME/utilities/js/libs/pjax/app.js`).

**Changed**

* Default `:focus-visible` accessibility styling (in `THEME/css/_elements.scss`).

**Fixed**

* Typo (in `THEME/js/navigation.js`).
* Preload fonts (in `THEME/media/fonts/fonts.php`).
* editor styles `cache_suffix` (in `THEME/utilities/inc/gutenberg/gutenberg.php`).

## 1.9.12 - 2024-08-05
**Added**

**Added**

* Custom-Select BE setting (in `THEME/utilities/js/libs/custom-select/custom-select.php`).

## 1.9.11 - 2024-08-02
**Fixed**

* Preload fonts (in `THEME/media/fonts/fonts.php`).

**Changed**

* Bubble custom accordion events (in `THEME/utilities/inc/gutenberg/inc/accordion/js/app.js`). 

## 1.9.10 - 2024-07-30
**Fixed**

* Immediately set `--rootWidth` (in `THEME/header.php`).

## 1.9.9 - 2024-07-29
**Added**

* `profiler` folder (in `THEME/docker/`).
* Dev layout display (in `THEME/utilities/inc/dev/inc/screen-size/app.js`).
* _Loosen Security_ theme setting (in `THEME/utilities/inc/security`).

**Changed**

* Moved `bodyclass` extension from `THEME/utilities/inc` to `THEME/utilities/inc/gutenberg/inc`.

**Fixed**

* Make `WORDPRESS_DEBUG` .env parameter overridable via `docker/.env.local`.
* `auto_include_files` script (in `THEME/utilities/auto-include-files.php`).

## 1.9.8 - 2024-07-25
**Changed**

* `editor-styles.scss` from utilities to theme.
* Icon font detached from `<i>`-tag.
* SCSS `fluid` function now works with all units.
* Support numeric input in `BREAKPOINTS.js` up/down methods.

**Added**

* Preload woff2 fonts (in `THEME/media/fonts/fonts.php`).
* bodyclass Gutenberg extension now with input for article.
* Docs for first setup steps _in_ WordPress.

## 1.9.7 - 2024-07-19
**Added**

* Display logo through `[bloginfo logo]` shortcode (in `THEME/utilities/inc/shortcodes/bloginfo.php`).

## 1.9.6 - 2024-07-19
**Added**

* Pjax options (in `THEME/utilities/js/libs/pjax/app.js`).
* Pjax BE setting (in `THEME/utilities/js/libs/pjax/pjax.php`).

**Fixed**

* Enqueue `THEME/utilities/js/behaviours.js` by default (in `THEME/functions.php`).

## 1.9.5 - 2024-07-16
**Added**

* Utilities docs.
* `WORDPRESS_ROOT` parameter (in `/docker`).

**Fixed**

* Custom width setter (in `THEME/utilities/js/custom-width.js`).
* Add `content` and `wide` to `BREAKPOINTS.js` (in `THEME/utilities/js/BREAKPOINTS.js`).
* Default `#masthead` and  `#colophon`

## 1.9.4 - 2024-07-12
**Added**

* Filter for security widget (in `THEME/utilities/inc/security/security.php`).
* `npm-run-all` node module to run multiple npm scripts.

## 1.9.3 - 2024-06-28
**Added**

* xDebug profiler (in `THEME/docker/xdebug.ini`).

## 1.9.2 - 2024-06-07
**Fixed**

* Security updates check (in `THEME/utilities/inc/security/inc/updates/updates.php`).

## 1.9.1 - 2024-06-07
**Added**

* `loader` mixin with parameters (in `THEME/utilities/css/mixins/loader.scss`).

## 1.9.0 - 2024-06-05
**Added**

* Custom form validation (in `THEME/utilities/js/libs/bouncer.min.js`).

**Fixed**

* `file-mods.php` security check.
* .gitignore
* t9n

## 1.8.3 - 2024-05-29
**Changed**

* All project's docker parameters are stored in the `.env` file (in `THEME/docker`).

## 1.8.2 - 2024-05-28
**Added**

* Check for accounts with identical login name and display name (in `THEME/utilities/inc/security/inc/usernames.php`).
* Disable updates (in `THEME/utilities/inc/security/inc/updates`).

## 1.8.1 - 2024-05-17
**Added**

* SPAM and file modification security checks (in `THEME/utilities/inc/security/inc`).

## 1.8.0 - 2024-05-10
**Added**

* Security checks (in `THEME/utilities/inc/security`).
* Images and `no-pjax` class to exclude from pjax redirect (in `THEME/utilities/js/libs/pjax/app.js`).

**Fixed**

* Set html attributes after pjax redirect.
* _Kill_ all gsap ScrollTriggers on pjax redirect (in `THEME/utilities/js/libs/pjax/app.js`).

## 1.7.1 - 2024-04-30
**Fixed**

* Custom 404 page (in `THEME/404.php`).
* Minor SCSS issues.

**Changed**

* Put _default_ CSS into utilities (`THEME/css/components/_blocks.scss` and `THEME/css/components/_content.scss` to `THEME/utilities/css/_misc.scss`)

**Added**

* RGBA hexadecimal notation (in `THEME/utilities/css/functions/_hexAlpha.scss`).

## 1.7.0 - 2024-03-22
**Changed**

* Put former `THEME/css/_utilities.scss` styles in `THEME/utilities/css/_misc.scss`.

**Fixed**

* Inline SVG `preserveAspectRatio` (in `THEME/utilities/js/inline-svg.js`).
* Variable call (in `THEME/utilities/css/_misc.scss`).

## 1.6.0 - 2024-03-20
**Added**

* Introduce js helper functions (`THEME/utilities/js/helpers.js`).

**Fixed**

* Block layout width (in `THEME/css/_utilities.scss`).
* Hide title check for "!" at first position (in `THEME/utilities/inc/hide-title.php`).

## 1.5.1 - 2024-03-16
**Changed**

* Children default `.alignfull` block layout (in `THEME/css/components/_content.scss`).

**Fixed**

* `content` and `wide` CSS `$breakpoints` (in `THEME/css/_variables.scss`).
* Editor style compilation on theme changes (in `THEME/_package.json`).

## 1.5.0 - 2024-03-15
**Added**

* Gutenberg accordion block.

## 1.4.1 - 2024-03-15
**Fixed**

* TYPEKIT_FONTS in `THEME/media/fonts/fonts.php`.

## 1.4.0 - 2024-03-14
**Added**

* All gsap plugins.
* [lottie-web](https://github.com/airbnb/lottie-web).

## 1.3.0 - 2024-03-13
**Added**

* [Air Datepicker](https://air-datepicker.com/).
* \<html> class input to posts (BE).
* Color helper function.
* Docker error handling tips.

**Fixed**

* Minor bugs.

## 1.2.4 - 2024-03-05
**Fixed**

* Editor styles.

## 1.2.3 - 2024-03-04
**Changed**

* WORDPRESS_DEBUG default value to 1 aka true).

## 1.2.2 - 2024-03-01
**Added**

* (formal) utilities translations.

## 1.2.1 - 2024-03-01
**Fixed**

* `shy` shortcode `document_title_parts` filter error.
* `document_title_parts` filter type error.

**Changed**

* Enable body class for _all_ post types.

## 1.2.0 - 2024-03-01
**Added**

* `hasH1()` check in content template.
* Hide title indicator ('!TITLE').

## 1.1.0 - 2024-03-01
**Added**

* DE (+ formal) translations.
* js: `isMobileDevice()`

**Fixed**

* Apply search/post option (en-/disable) in templates.

## 1.0.2 - 2024-02-13
**Fixed**

* Dev js.

## 1.0.1 - 2024-02-12
**Changed**

* Change order of auto include files: Utilities vs theme.

## 1.0.0 - 2024-02-06
**init**