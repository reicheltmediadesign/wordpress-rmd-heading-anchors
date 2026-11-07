<?php
/**
 * Settings → Heading anchors, saved with the Settings API.
 *
 * @package RMD\HeadingAnchors
 */

namespace RMD\HeadingAnchors\Admin;

use RMD\HeadingAnchors\Domain\Settings as SettingsData;
use RMD\HeadingAnchors\Domain\Slugger;
use RMD\HeadingAnchors\Plugin;
use RMD\HeadingAnchors\Settings;

defined( 'ABSPATH' ) || exit;

final class SettingsPage {

	public const SLUG = 'rmd-heading-anchors';

	public static function init(): void {
		add_action( 'admin_menu', [ self::class, 'menu' ] );
		add_filter( 'option_page_capability_' . Settings::GROUP, static fn(): string => Plugin::CAP );
		add_filter( 'plugin_action_links_' . RMD_HA_BASENAME, [ self::class, 'action_links' ] );
	}

	public static function menu(): void {
		add_options_page(
			__( 'Heading anchors', 'rmd-heading-anchors' ),
			__( 'Heading anchors', 'rmd-heading-anchors' ),
			Plugin::CAP,
			self::SLUG,
			[ self::class, 'render' ]
		);
	}

	public static function url(): string {
		return add_query_arg( [ 'page' => self::SLUG ], admin_url( 'options-general.php' ) );
	}

	/**
	 * @param string[] $links Plugin action links.
	 * @return string[]
	 */
	public static function action_links( $links ): array {
		$links = (array) $links;
		array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( self::url() ), esc_html__( 'Settings', 'rmd-heading-anchors' ) ) );
		return $links;
	}

	public static function render(): void {
		if ( ! current_user_can( Plugin::CAP ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'rmd-heading-anchors' ) );
		}

		$settings = Settings::get();
		$name     = Settings::OPTION;

		echo '<div class="wrap rmd-ha">';
		echo '<h1>' . esc_html__( 'Heading anchors', 'rmd-heading-anchors' ) . '</h1>';
		echo '<p>' . esc_html__( 'Headings in the content get an ID automatically, so you can link to a section with #id. Headings that already have an ID (for example from the "HTML anchor" field in the block editor) keep it.', 'rmd-heading-anchors' ) . '</p>';

		echo '<form method="post" action="options.php">';
		settings_fields( Settings::GROUP );
		echo '<table class="form-table" role="presentation"><tbody>';

		echo '<tr><th scope="row">' . esc_html__( 'Heading levels', 'rmd-heading-anchors' ) . '</th><td><fieldset>';
		printf( '<input type="hidden" name="%s[levels][]" value="0">', esc_attr( $name ) );
		foreach ( SettingsData::LEVELS as $level ) {
			printf(
				'<label for="rmd-ha-level-%1$d"><input type="checkbox" id="rmd-ha-level-%1$d" name="%2$s[levels][]" value="%1$d" %3$s> H%1$d</label><br>',
				(int) $level,
				esc_attr( $name ),
				checked( in_array( $level, $settings['levels'], true ), true, false )
			);
		}
		echo '<p class="description">' . esc_html__( 'H1 is usually the page title and not part of the content.', 'rmd-heading-anchors' ) . '</p>';
		echo '</fieldset></td></tr>';

		echo '<tr><th scope="row">' . esc_html__( 'Filler words', 'rmd-heading-anchors' ) . '</th><td>';
		printf( '<input type="hidden" name="%s[filler_words]" value="0">', esc_attr( $name ) );
		printf(
			'<label for="rmd-ha-filler-words"><input type="checkbox" id="rmd-ha-filler-words" name="%s[filler_words]" value="1" %s> %s</label>',
			esc_attr( $name ),
			checked( $settings['filler_words'], true, false ),
			esc_html__( 'Leave filler words out of the IDs', 'rmd-heading-anchors' )
		);
		echo '<p class="description">' . esc_html__( 'A heading made only of filler words keeps them. Built-in list:', 'rmd-heading-anchors' ) . ' <code>' . esc_html( implode( ', ', Slugger::DEFAULT_FILLER_WORDS ) ) . '</code></p>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="rmd-ha-extra-filler-words">' . esc_html__( 'More filler words', 'rmd-heading-anchors' ) . '</label></th><td>';
		printf(
			'<textarea id="rmd-ha-extra-filler-words" name="%s[extra_filler_words]" rows="3" class="large-text">%s</textarea>',
			esc_attr( $name ),
			esc_textarea( implode( ', ', $settings['extra_filler_words'] ) )
		);
		echo '<p class="description">' . esc_html__( 'Separated by commas, spaces or line breaks. Only used when filler words are left out.', 'rmd-heading-anchors' ) . '</p>';
		echo '</td></tr>';

		echo '</tbody></table>';
		submit_button();
		echo '</form>';

		self::render_examples();
		echo '</div>';
	}

	private static function render_examples(): void {
		$slugger  = Settings::slugger();
		$examples = [
			__( 'About us & our services', 'rmd-heading-anchors' ),
			'Häufige Fragen zur Anfahrt',
			'Öffnungszeiten für Ostern',
		];

		echo '<h2>' . esc_html__( 'Examples with the saved settings', 'rmd-heading-anchors' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr>';
		echo '<th>' . esc_html__( 'Heading', 'rmd-heading-anchors' ) . '</th><th>' . esc_html__( 'Anchor', 'rmd-heading-anchors' ) . '</th>';
		echo '</tr></thead><tbody>';
		foreach ( array_unique( $examples ) as $example ) {
			printf( '<tr><td>%s</td><td><code>#%s</code></td></tr>', esc_html( $example ), esc_html( $slugger->slugify( $example ) ) );
		}
		echo '</tbody></table>';
	}
}
