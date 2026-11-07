# Changelog

All notable changes to this plugin are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [0.2.0] – 2026-11-07

### Added

- Block editor: "Anchor" panel in the settings of every heading block. Shows the automatic anchor (computed by the server with the same code as the output), fixes it with one click or takes an own anchor, and warns when two headings use the same anchor. Fixed anchors are stored in the block's `anchor` attribute (*HTML anchor*) and stay when the heading text changes.
- REST route `rmd-heading-anchors/v1/anchors` (POST, `edit_posts`) for the panel.

## [0.1.0] – 2026-11-07

### Added

- Automatic anchor IDs for headings in the content (`the_content`), added on output only: lowercase, umlauts spelled out (`ä` → `ae`), other accents removed, special characters and spaces turned into hyphens, at most 60 characters.
- Filler words (German and English articles, conjunctions, common prepositions) are left out; own words can be added. Filter `rmd_ha_filler_words`.
- Existing IDs are kept and reserved; repeated headings get `-2`, `-3`, …
- Settings → Heading anchors: heading levels (H2–H6 by default), filler words, examples with the current settings.
- Updates from GitHub releases.
