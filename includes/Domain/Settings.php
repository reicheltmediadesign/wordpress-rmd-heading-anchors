<?php
/**
 * The only place that defines the shape of the stored settings. Free of
 * WordPress so it can be tested with plain PHPUnit.
 *
 * @package RMD\HeadingAnchors
 */

declare(strict_types=1);

namespace RMD\HeadingAnchors\Domain;

final class Settings {

	public const LEVELS = [ 1, 2, 3, 4, 5, 6 ];

	public const EXTRA_FILLER_WORDS_MAX = 200;

	/**
	 * @return array{levels: list<int>, filler_words: bool, extra_filler_words: list<string>}
	 */
	public static function defaults(): array {
		return [
			'levels'             => [ 2, 3, 4, 5, 6 ],
			'filler_words'       => true,
			'extra_filler_words' => [],
		];
	}

	/**
	 * Reads unknown data (stored option or form input). Invalid or missing
	 * values fall back to the default; unknown keys are dropped.
	 *
	 * @param mixed $raw Raw value.
	 * @return array{levels: list<int>, filler_words: bool, extra_filler_words: list<string>}
	 */
	public static function parse( $raw ): array {
		$defaults = self::defaults();
		$raw      = is_array( $raw ) ? $raw : [];

		$levels = $defaults['levels'];
		if ( isset( $raw['levels'] ) && is_array( $raw['levels'] ) ) {
			$levels = [];
			foreach ( $raw['levels'] as $level ) {
				if ( ( is_int( $level ) || ( is_string( $level ) && ctype_digit( $level ) ) ) && in_array( (int) $level, self::LEVELS, true ) ) {
					$levels[] = (int) $level;
				}
			}
			$levels = array_values( array_unique( $levels ) );
			sort( $levels );
		}

		return [
			'levels'             => $levels,
			'filler_words'       => self::bool( $raw['filler_words'] ?? null, $defaults['filler_words'] ),
			'extra_filler_words' => self::words( $raw['extra_filler_words'] ?? null ),
		];
	}

	/**
	 * Filler words used by the slugger for these settings.
	 *
	 * @param array{levels: list<int>, filler_words: bool, extra_filler_words: list<string>} $settings Parsed settings.
	 * @return list<string>
	 */
	public static function filler_words( array $settings ): array {
		if ( ! $settings['filler_words'] ) {
			return [];
		}
		return array_values( array_unique( array_merge( Slugger::DEFAULT_FILLER_WORDS, $settings['extra_filler_words'] ) ) );
	}

	/**
	 * @param mixed $value Raw value.
	 */
	private static function bool( $value, bool $fallback ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_int( $value ) || ( is_string( $value ) && in_array( $value, [ '0', '1' ], true ) ) ) {
			return 1 === (int) $value;
		}
		return $fallback;
	}

	/**
	 * Accepts a list or text separated by commas, spaces or line breaks; words
	 * are normalised like heading text, so "für" matches "fuer".
	 *
	 * @param mixed $value Raw value.
	 * @return list<string>
	 */
	private static function words( $value ): array {
		if ( is_array( $value ) ) {
			$value = implode( ' ', array_filter( $value, 'is_string' ) );
		}
		if ( ! is_string( $value ) ) {
			return [];
		}
		return array_slice( array_values( array_unique( Slugger::words( $value ) ) ), 0, self::EXTRA_FILLER_WORDS_MAX );
	}
}
