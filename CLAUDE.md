# RMD Heading Anchors – Projektregeln

WordPress-Plugin von reichelt media.design: automatische Anker-IDs für Überschriften im Inhalt (Einstellungen → Heading anchors, nur `manage_options`). Repo: `github.com/reicheltmediadesign/wordpress-rmd-heading-anchors`, Lizenz GPL-2.0-or-later, Autor Philipp Reichelt. Aufbau und Release wie `rmd-site-settings` (ohne npm/Build-Schritt).

## Aufbau

- `rmd-heading-anchors.php` Bootstrap (muss PHP-7-parsebar bleiben), `includes/` PSR-4 `RMD\HeadingAnchors\`.
- `includes/Domain/` ist **WordPress-frei** und wird mit plain PHPUnit getestet: `Settings` (einzige Stelle für die Datenform der Option `rmd_ha_settings`, `defaults()`/`parse()`), `Slugger` (Text → ID, Füllwortliste `DEFAULT_FILLER_WORDS`), `HeadingAnchors` (setzt IDs in einem HTML-Fragment).
- `Slugger`: Umlaute zuerst ausschreiben (`GERMAN`), dann `transliterator_transliterate` (intl), sonst `remove_accents()` von WordPress. Füllwörter werden in derselben Form verglichen (`Slugger::words()`), Einträge in `DEFAULT_FILLER_WORDS` also schon transliteriert (`fuer`). Ein Test prüft das.
- `HeadingAnchors` arbeitet per Regex, nicht mit `WP_HTML_Processor`, damit die Domain WordPress-frei bleibt. IDs sind pro Fragment eindeutig und deterministisch – **kein** request-weiter Zähler, weil SEO-Plugins `the_content` mehrfach aufrufen und sich die IDs sonst verschieben würden.
- `Modules\ContentAnchors`: `the_content` Priorität 12 (nach Blöcken 9, wpautop 10, Shortcodes 11), nicht im Admin. Inhalt in der Datenbank wird nie verändert.
- Zugriff: `manage_options`. Multisite: Einstellungen pro Site, Uninstall über alle Sites.

## Code-Regeln

- PHP 8.1+, WP 6.8+. WordPress-Coding-Standards (`phpcs.xml.dist`), Methoden snake_case, Präfix `rmd_ha_` / `RMD_HA_` / Namespace `RMD\HeadingAnchors`, Textdomain `rmd-heading-anchors`.
- Quellstrings Englisch, Übersetzung in `languages/rmd-heading-anchors-de_DE.po` (Sie-Form) von Hand nachziehen; `de_DE_formal` erzeugt `bin/make-i18n.sh` daraus (braucht WP-CLI; lokal nicht installiert, im Release-Workflow vorhanden).
- Version an drei Stellen: Plugin-Header, `RMD_HA_VERSION`, `readme.txt` (Stable tag). `bash bin/check-version.sh` prüft das.
- Änderungen am Slug-Verfahren ändern IDs auf bestehenden Seiten und brechen Links: nur bewusst, im Changelog unter „Changed“ deutlich vermerken.

## Build und Prüfung

```powershell
composer phpcs         # PHPCS
composer test          # PHPUnit (Domain)
bash bin/check-version.sh
bash bin/build-zip.sh  # dist/rmd-heading-anchors.zip (Git Bash: zip, composer nötig)
```

Composer liegt unter `C:\xampp\php\composer.bat`. Lokal testen: `.\bin\link-local.ps1 -WordPressPath "C:\xampp\htdocs\testwordpress"` legt eine Junction in `wp-content\plugins` an. Kundenseiten bekommen nur das Release-Zip.

## Git und Release

- Committen nur auf Anfrage, auf Feature-Branches; nie direkt auf `main`.
- Release: Versionen an allen Stellen erhöhen, `CHANGELOG.md` und `readme.txt` (Changelog) ergänzen, committen, Tag `vX.Y.Z` pushen. Der Workflow lintet, testet, baut Übersetzungen, erzeugt `rmd-heading-anchors.zip` und `update.json` und legt ein **Draft-Release** an. Beide Assets müssen am Release bleiben (Update-Check liest `releases/latest/download/update.json`).
- Den vom Workflow angelegten Entwurf bearbeiten (Releases → Entwurf → Stift), nicht neu anlegen.
- Bei jedem Release Titel `vX.Y.Z – <Kernänderung>` und Release Notes (Englisch, Markdown in Codeblock) mit ausgeben; Grundlage `git log <letzter Tag>..HEAD`.
- `gh` ist installiert (ggf. mit vollem Pfad `C:\Program Files\GitHub CLI\gh.exe`); Workflow-Status alternativ über `https://api.github.com/repos/reicheltmediadesign/wordpress-rmd-heading-anchors/actions/runs`.
- Topics fürs Repo: `wordpress`, `wordpress-plugin`, `anchor-links`, `headings`, `seo`.

## README

Nutzerorientiert (Features, Usage, Settings, Privacy, License). Kein Development-Abschnitt in der README; Build/Release steht hier.
