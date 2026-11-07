<?php
/**
 * Plugin bootstrap: wires all parts to WordPress hooks.
 *
 * @package RMD\HeadingAnchors
 */

namespace RMD\HeadingAnchors;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	/** Only administrators change the settings. */
	public const CAP = 'manage_options';

	private static bool $booted = false;

	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;

		add_action( 'init', [ self::class, 'load_textdomain' ], 1 );
		add_action( 'init', [ Upgrade::class, 'run' ], 5 );

		Updater::init();
		Settings::init();

		Modules\ContentAnchors::init();

		if ( is_admin() ) {
			Admin\SettingsPage::init();
		}
	}

	public static function load_textdomain(): void {
		load_plugin_textdomain( 'rmd-heading-anchors', false, dirname( RMD_HA_BASENAME ) . '/languages' );
	}
}
