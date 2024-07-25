_s
===

Hi. I'm a fork of the starter theme called _s. I'm a template theme meant to be the next, most awesome, WordPress theme out there. That's what I'm here for.

## Requirements

`_s` requires the following dependencies:

- [Node.js](https://nodejs.org/)

## Quick Start

### Docker DEV Environment (optional)

Move the `/docker` folder to the root of the project's installation and set parameters in the `.env` file.

1. Replace `PROJECT_NAME` with the current project name.
2. Replace `PROJECT_ABBR` with the project's abbreviation. This is used as DB table prefix. Default is `wp`.
3. Replace `DEV_URL_WITHOUT_SCHEMA` with the project's dev URL without `http://`. This should be the live URL with `dev.` instead of `www.`: dev.project.de
4. Replace `STAGE_URL_WITH_SCHEMA` with the project's stage URL.
5. Replace `LIVE_URL_WITH_SCHEMA` with the project's live URL.

6. Maybe adjust `WORDPRESS_ROOT` path if the WordPress installation isn't project's root.

For further Docker installation see docker/README.md

### Theme

Change the theme's name "_s" to something else (like, say, `next-awesome-theme`), and then you'll need to do a five-step find and replace on the name in all the templates.

1. Search for `'_s'` (inside single quotations) to capture the text domain and replace with: `'next-awesome-theme'`.
2. Search for `_s_` to capture all the functions names and replace with: `megatherium_is_awesome_`.
3. Search for `Text Domain: _s` in `css/style.scss` and replace with: `Text Domain: next-awesome-theme`.
4. Search for `_s.pot` and replace with: `next-awesome-theme.pot`.
5. Search for `_s-` to capture prefixed handles and replace with: `next-awesome-theme-`.

Then, update the stylesheet header in `css/style.scss` and rename `_s.pot` from `languages` folder to use the theme's slug.

## Setup

To start using all the tools that come with `_s`  you need to install the necessary Node.js dependencies:

```sh
$ npm install
```

## Development

`_s` comes with CLI commands tailored for WordPress theme development:

### Available CLI commands

- `npm run watch` : watches _all_ SASS and JS files and recompiles them when they change.
- `npm run build` : compiles _all_ SASS and JS files for production use.

### Icon font

You can easily create your own icon font. Put all your svg icons in `/media/fonts/Icont/icons`
and run `npm run icont:generate`.

List all your icons (alphabetical order) in `/css/_variables.scss` to create a map
to convenient access the content declaration of the icons: e.g. `map-get($Icont, NAME)`;

### Utilities

`_s` comes packed with a bunch of CSS, JS and PHP helpers. For further information see files itself.

> [!CAUTION]
> **Never ever change anything in the utilities folder!**
> This folder is used cross-project and should be updated from time to time. All changes would be overridden.
> Please contact the _administrator_ if you discover problems or have ideas for improvement.

#### CSS

##### `/utilities/css/functions/`

| File                       | What does it do                  |
|----------------------------|----------------------------------|
| _currentColor-opacity.scss | Apply opacity to `currentColor`. |
| _fluid.scss                | Calculate responsive values.     |
| _hexAlpha.scss             | Hexadecimal RGBA notation.       |

##### `/utilities/css/mixins/`

| File                    | What does it do                                          |
|-------------------------|----------------------------------------------------------|
| _breakpoints.scss       | Get breakpoints (from `$breakpoints`) and media queries. |
| _dir.scss               | Wrap CSS especially for ltr or rtl direction.            |
| _loader.scss            | Pseudo loading animation.                                |
| _pseudons.scss          | Pseudo element icons.                                    |
| _px2rem.scss            | Convert `px` value to type `rem`.                        |
| _scrollwithoutbars.scss | Hide scrollbars but keep functionality.                  |
| _sr-only.scss           | Hide element on all devices except screen readers.       |

#### JS

| File              | What does it do                                       |
|-------------------|-------------------------------------------------------|
| alter.js          | _Equivalent_ to WP's `apply_filter()`/`add_filter()`. |
| behaviours.js     | Wrapper for functions to be called grouped.           |
| cache.js          | Cache values as cookie or in localStorage.            |
| custom-width.js   | Custom widths from classes.                           |
| external-links.js | Open external links in new window.                    |
| helpers.js        | ...                                                   |
| inline-svg.js     | Replace SVG image with actual SVG code.               |

##### `/utilities/js/libs/`

| Library                                                         | What does it do                                                              |
|-----------------------------------------------------------------|------------------------------------------------------------------------------|
| [Air Datepicker](https://air-datepicker.com/)                   | Datepicker UI.                                                               |
| autosize                                                        | [Autosize `<textarea>`](https://www.jacklmoore.com/autosize/) and `<input>`. | 
| [Custom-select](https://custom-select.github.io/custom-select/) | Custom `<select>` creation.                                                  | 
| [GSAP](https://gsap.com/)                                       | Animate anything.                                                            | 
| [lottie-web](https://github.com/airbnb/lottie-web)              | JSON animations rendered natively.                                           | 
| [Pjax](https://github.com/MoOx/pjax)                            | Use AJAX to deliver a fast browsing experience.                              | 

#### PHP

| `/utilities/inc/` | What does it do                                                |
|-------------------|----------------------------------------------------------------|
| bodyclass         | Add Gutenberg setting to add html/body classes.                |
| dev               | Frontend info panel for development.                           |
| image-orientation | Add orientation class (landscape, square, portrait) to images. |
| post-edit-link    | Add post edit link to title.                                   |
| security          | Security features. See BE dashboard.                           |

## WordPress

1. Remove example post.
2. Disallow comments in `/wp-admin/options-discussion.php`.
3. Set permalink structure to **Post name** in `/wp-admin/options-permalink.php`.
4. Clear sidebar widgets in `/wp-admin/widgets.php`.
5. Create 2 menus. One for main menu (navigation) and one for footer. See `/wp-admin/nav-menus.php`.

Find further theme settings (Pjax, ...) in customizer. 

---

Now you're ready to go! The next step is easy to say, but harder to do: make an awesome WordPress theme. :)

Happy coding and good luck!
