# Icon font

Put all icons which should be included into the icon font in here.

> [!CAUTION]
> Icons mustn't include strokes. Areas only!

## Generate icon font

Execute `$ npm run icont:generate` in your theme's root. This will generate the icon font itself
and its CSS which is already enqueued by default.

Insert each icon by its name in alphabetical order in the list of icons in `THEME/css/_variables.scss`.

```scss
@each $icon in ( ICON_NAME, ICON_NAME, ... ) { ... }
```

## Usage

```scss
SELECTOR {
  @include icon-before(ICON_NAME);
  // same as:
  &:before {
    @include icon(ICON_NAME);
  }
  
  @include icon-after(ICON_NAME);
  // same as:
  &:after {
    @include icon(ICON_NAME);
  }
}

// you can also use other icon fonts
// !!! but you have to use the ICON_CODE instead !!!
SELECTOR {
  @include icon-before("\f529", dashicons);
  // ...
}
```


