<?php
/**
 * Adds the anchor IDs when post content is output. The stored content is
 * never changed, so deactivating the plugin removes all generated IDs.
 *
 * @package RMD\HeadingAnchors
 */

namespace RMD\HeadingAnchors\Modules;

use RMD\HeadingAnchors\Domain\HeadingAnchors;
use RMD\HeadingAnchors\Settings;

defined( 'ABSPATH' ) || exit;

final class ContentAnchors {

	/** After blocks (9), wpautop (10) and shortcodes (11), so their headings are included. */
	private const PRIORITY = 12;

	private static ?HeadingAnchors $anchors = null;

	public static function init(): void {
		add_filter( 'the_content', [ self::class, 'filter' ], self::PRIORITY );
		add_action( 'update_option_' . Settings::OPTION, [ self::class, 'reset' ] );
	}

	/**
	 * @param mixed $content Post content.
	 * @return mixed
	 */
	public static function filter( $content ) {
		if ( ! is_string( $content ) || '' === $content || is_admin() ) {
			return $content;
		}
		self::$anchors ??= Settings::anchors();
		return self::$anchors->apply( $content );
	}

	public static function reset(): void {
		self::$anchors = null;
	}
}
