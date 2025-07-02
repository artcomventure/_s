# Changelog

## 1.14.1 - 2025-07-02
**Added**

* Exclude `_original_post` (Bogo) from being duplicated (Yoast Duplicate Post) (in `THEME/utilities/inc/bogo/inc/duplicate-post.php`).

## 1.14.0 - 2025-04-04
**Added**

* Heading font sizes (in `THEME/theme.json`).
* Custom string translations (in `THEME/languages/_s+.pot`).
* Localizable menus for bogo (in `THEME/utilities/inc/plugins/bogo/inc/localize-menus.php`).
* _No line break_ text format (in `THEME/utilities/inc/gutenberg/src/index.js`).
* Validate CF7 email (in `THEME/utilities/inc/plugins/contact-form-7/inc/validate-email`).
* CF7 mail sent OK redirect (in `THEME/utilities/inc/plugins/contact-form-7/inc/mail-sent-ok-redirect`).
* AIOSEO breadcrumbs fix for not queryable taxonomies (in `THEME/utilities/inc/plugins/aioseo.php`).
* Order by setting for posts-list (in `THEME/utilities/inc/posts-list`).
* Disable auto-updates (in `THEME/utilities/inc/security/inc/updates/updates.php`).

**Fixed**
* Various CSS.
* pjax transition (in `THEME/utilities/js/libs/pjax`).

## 1.13.2 - 2025-01-21
**Changed**

* Bug fixes.
* Accordion a11y (in `THEME/utilities/inc/gutenberg/inc/accordion/js/app.js`).
* Specific posts for _posts list block_ (in `THEME/utilities/inc/posts-list`).

## 1.13.1 - 2024-11-26
**Added**

* Default _above-the-fold_ css (in `THEME/css/above-the-fold.scss`).
* Prioritize files (theme's style.css and featured images).
* More capabilities for user role editor (in `THEME/utilities/inc/role.php`).

**Changed**

* Improve a11y shy aria-label.
* Block layout.
* Enable classic widget editor by default.

## 1.13.0 - 2024-010-28
**Added**

* Post teaser Block (in `THEME/utilities/inc/posts-list`).

## 1.12.0 - 2024-08-24
**Added**

* Accessibility feature (in `THEME/utilities/inc/accessibility`).
* Image block aspect ratio (in `THEME/utilities/inc/gutenberg/inc/aspect-ratio`).
* Video _handling_ (in `THEME/utilities/inc/video`).
* Placeholder images (in `THEME/media`).

**Changed**

* Templates (in `THEME/inc/template-tags.php` and `THEME/template-parts`);
* Remove core patterns (in `THEME/utilities/inc/gutenberg/gutenberg.php`).
* Enable responsive embeds support (in `THEME/functions.php`).

**Various**

* Bug fixes.

## 1.11.2 - 2024-08-13
**Added**

* JS translations to accordion (in `THEME/utilities/inc/gutenberg/inc/accordion`).
* Accordion a11y (in `THEME/utilities/inc/gutenberg/inc/accordion`).
* Pjax support to data-href (in `THEME/utilities/inc/gutenberg/inc/data-href`).

**Fixed**

* Gutenberg `BREAKPOINTS` (in `THEME/utilities/inc/gutenberg/inc/hide`).

## 1.11.1 - 2024-08-09
**Changed**

* Default button style (in `THEME/css/components/_form.scss`).
* Default link style (in `THEME/css/_elements.scss`).
* Optional display header text (in `THEME/header.php`).

**Added**

* `$lightness-threshold` SCSS variable (in `THEME/css/_variables.scss`).
* Inline svg accessibility (in `THEME/utilities/js/inline-svg.js`).

## 1.11.0 - 2024-08-08
**Added**

* Setting for hiding blocks (in `THEME/inc/gutenberg/inc/hide`).
* `[data-href]` _open in new window_ setting (in `THEME/inc/gutenberg/inc/data-href`).
* Default colors (in `THEME/theme.json`).

## 1.10.2 - 2024-08-07
**Added**

* Theme screenshot (in `THEME/screenshot.png|psd`).

## 1.10.1 - 2024-08-07
**Added**

* `[data-href]` placeholder (in `THEME/utilities/gutenberg/inc/data-href/app.js`).
* Custom select translations (in `THEME/utilities/`)

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