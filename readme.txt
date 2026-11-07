=== RMD Heading Anchors ===
Contributors: reicheltmediadesign
Tags: anchor, heading, id, jump links, table of contents
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gives every heading in the content an automatic anchor ID, so sections can be linked directly.

== Description ==

RMD Heading Anchors adds an ID to every heading in posts and pages when they are shown, so you can link straight to a section. The IDs are short and readable.

* "Häufige Fragen zur Anfahrt" becomes #haeufige-fragen-anfahrt: lowercase, umlauts spelled out, other accents removed, special characters and spaces turned into hyphens.
* Filler words such as articles, conjunctions and common prepositions (German and English) are left out; you can add your own.
* Headings that already have an ID, for example from the "HTML anchor" field in the block editor, keep it.
* Repeated headings get -2, -3, …; the same content always gives the same IDs.
* Choose the heading levels, H2–H6 by default.
* Nothing is written into the content; deactivating the plugin removes the IDs again.

== Installation ==

1. Upload the plugin zip under Plugins → Add New → Upload.
2. Activate the plugin.
3. Optional: adjust the settings under Settings → Heading anchors.

== Frequently Asked Questions ==

= How do I find the ID of a heading? =

Look at the page source or use the browser's developer tools. To choose an ID yourself, fill in the "HTML anchor" field of the heading block; the plugin keeps it.

= What happens when I change the settings? =

The IDs of existing headings may change. Links that already point to an anchor may need to be updated.

== Changelog ==

= 0.1.0 =
* Initial release.
