# RMD Heading Anchors

Gives every heading in the content an automatic anchor ID, so you can link straight to a section – `/leistungen/#webdesign` instead of "scroll down to …". The IDs are short and readable: lowercase, umlauts spelled out, special characters and spaces turned into hyphens, filler words left out.

## Features

- **Automatic IDs** – headings in posts and pages get an `id` when the page is shown. Works with the block editor, the classic editor and shortcodes.
- **Readable anchors** – `Häufige Fragen zur Anfahrt` becomes `#haeufige-fragen-anfahrt`: lowercase, `ä/ö/ü/ß` as `ae/oe/ue/ss`, other accents removed (`Café` → `cafe`), everything else between words turned into a single hyphen. IDs longer than 60 characters are cut at a word boundary.
- **Filler words left out** – articles, conjunctions and common prepositions in German and English (*der, die, das, und, für, zur, the, and, of …*), plus your own list. A heading made only of filler words keeps them.
- **Own anchors win** – headings that already have an ID, for example from the *HTML anchor* field in the block editor, are left alone.
- **Fixed anchors in the block editor** – the block settings of every heading show an *Anchor* panel with the heading's current automatic anchor. One click fixes it, so links keep working when the heading text changes later; or enter an anchor of your own. A warning appears when two headings use the same anchor.
- **Unique and stable** – a heading text that appears twice gets `-2`, `-3`, …; IDs already used elsewhere in the content are skipped. The same content always gives the same IDs, so links keep working.
- **Nothing stored in the content** – automatic IDs are added on output only. Deactivating the plugin removes them again; only anchors you fixed stay.

## Usage

Activate the plugin – that's it. To link to a section, open the page, find the heading's ID (browser developer tools, or the examples on the settings page for the pattern) and link to `#id` on the same page or `/page/#id` from elsewhere.

In the block editor, select a heading: the *Anchor* panel in the block settings shows its automatic anchor.

- **Fix automatic anchor** stores the current anchor with the heading. It stays the same when you change the heading text, so existing links keep working.
- **Fixed anchor** lets you enter an anchor of your own instead (spaces and `#` become hyphens).
- **Use automatic anchor** removes the fixed anchor again.

A fixed anchor is the same as the block's *Advanced → HTML anchor* field and is saved as the heading's `id` in the content, so it also stays when the plugin is deactivated.

Settings are under **Settings → Heading anchors** (administrators only).

## Settings

| Setting | Default | Description |
| --- | --- | --- |
| Heading levels | H2–H6 | Which heading levels get an ID. H1 is usually the page title and not part of the content. |
| Filler words | on | Leave filler words out of the IDs. The settings page lists the built-in words. |
| More filler words | empty | Own filler words, separated by commas, spaces or line breaks. Umlauts may be typed as such (`für` matches `fuer`). |

The settings page shows a few example headings with the anchors the current settings produce.

Changing the settings changes the automatic IDs of existing headings. Links that already point to an anchor may need to be updated – fix the anchors of headings you link to.

### For developers

The filter `rmd_ha_filler_words` receives the list of filler words (lowercase ASCII, umlauts spelled out) and may change it.

## Privacy

The plugin stores its settings in the option `rmd_ha_settings` and sends no data anywhere. The *Anchor* panel asks the site's own REST API (`rmd-heading-anchors/v1/anchors`, for users who can edit posts) for the automatic anchors. The only outgoing request is the update check, which reads `update.json` from the GitHub releases of this repository.

## Installation

1. Download `rmd-heading-anchors.zip` from the [latest release](https://github.com/reicheltmediadesign/wordpress-rmd-heading-anchors/releases/latest).
2. In WordPress go to **Plugins → Add New → Upload Plugin**, choose the zip and activate it.
3. Updates appear on the Plugins screen like any other plugin update.

Deleting the plugin removes its options on every site of a network.

## License

GPL-2.0-or-later, © Philipp Reichelt, [reichelt media.design](https://reicheltmedia.design)
