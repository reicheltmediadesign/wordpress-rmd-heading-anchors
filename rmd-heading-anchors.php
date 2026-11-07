<?php
/**
 * Plugin Name:       RMD Heading Anchors
 * Plugin URI:        https://github.com/reicheltmediadesign/wordpress-rmd-heading-anchors
 * Description:       Gives every heading in the content an automatic anchor ID, so sections can be linked directly: readable, umlauts spelled out, filler words removed.
 * Version:           0.2.0
 * Requires at least: 6.8
 * Requires PHP:      8.1
 * Author:            Philipp Reichelt, reichelt media.design
 * Author URI:        https://reicheltmedia.design
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rmd-heading-anchors
 * Domain Path:       /languages
 * Update URI:        https://github.com/reicheltmediadesign/wordpress-rmd-heading-anchors
 *
 * @package RMD\HeadingAnchors
 */

// This file must stay parseable by PHP 7.x so the version notice below can be shown instead of a fatal error.

defined( 'ABSPATH' ) || exit;

define( 'RMD_HA_VERSION', '0.2.0' );
define( 'RMD_HA_FILE', __FILE__ );
define( 'RMD_HA_DIR', plugin_dir_path( __FILE__ ) );
define( 'RMD_HA_URL', plugin_dir_url( __FILE__ ) );
define( 'RMD_HA_BASENAME', plugin_basename( __FILE__ ) );

if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>';
			echo esc_html(
				sprintf(
					/* translators: 1: required PHP version, 2: current PHP version */
					__( 'RMD Heading Anchors requires PHP %1$s or newer. This site runs PHP %2$s, so the plugin stays inactive.', 'rmd-heading-anchors' ),
					'8.1',
					PHP_VERSION
				)
			);
			echo '</p></div>';
		}
	);
	return;
}

spl_autoload_register(
	function ( $class_name ) {
		$prefix = 'RMD\\HeadingAnchors\\';
		if ( 0 !== strncmp( $class_name, $prefix, strlen( $prefix ) ) ) {
			return;
		}
		$relative = substr( $class_name, strlen( $prefix ) );
		$file     = RMD_HA_DIR . 'includes/' . str_replace( '\\', '/', $relative ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

if ( is_readable( RMD_HA_DIR . 'vendor/autoload.php' ) ) {
	require RMD_HA_DIR . 'vendor/autoload.php';
}

RMD\HeadingAnchors\Plugin::boot();
