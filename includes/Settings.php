<?php
/**
 * Plugin settings stored in one option, registered with the Settings API.
 *
 * @package RMD\HeadingAnchors
 */

namespace RMD\HeadingAnchors;

use RMD\HeadingAnchors\Domain\HeadingAnchors;
use RMD\HeadingAnchors\Domain\Settings as SettingsData;
use RMD\HeadingAnchors\Domain\Slugger;

defined( 'ABSPATH' ) || exit;

final class Settings {

	public const OPTION = 'rmd_ha_settings';
	public const GROUP  = 'rmd_ha_settings_group';

	private static ?array $cache = null;

	public static function init(): void {
		add_action( 'init', [ self::class, 'register' ] );
		add_action( 'update_option_' . self::OPTION, [ self::class, 'flush' ] );
		add_action( 'add_option_' . self::OPTION, [ self::class, 'flush' ] );
	}

	public static function register(): void {
		register_setting(
			self::GROUP,
			self::OPTION,
			[
				'type'              => 'array',
				'default'           => SettingsData::defaults(),
				'show_in_rest'      => false,
				'sanitize_callback' => [ SettingsData::class, 'parse' ],
			]
		);
	}

	/**
	 * @return array{levels: list<int>, filler_words: bool, extra_filler_words: list<string>}
	 */
	public static function get(): array {
		if ( null === self::$cache ) {
			self::$cache = SettingsData::parse( get_option( self::OPTION, [] ) );
		}
		return self::$cache;
	}

	public static function flush(): void {
		self::$cache = null;
	}

	public static function slugger(): Slugger {
		/**
		 * Filters the words that are left out of anchor IDs. Words are lowercase
		 * ASCII with umlauts spelled out ("fuer", not "für").
		 *
		 * @param list<string> $words Filler words; empty when the setting is off.
		 */
		$words = apply_filters( 'rmd_ha_filler_words', SettingsData::filler_words( self::get() ) );
		$words = is_array( $words ) ? array_values( array_filter( $words, 'is_string' ) ) : [];
		return new Slugger( $words );
	}

	public static function anchors(): HeadingAnchors {
		return new HeadingAnchors( self::slugger(), self::get()['levels'] );
	}
}
