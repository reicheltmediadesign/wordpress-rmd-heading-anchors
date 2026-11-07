<?php
/**
 * Turns heading text into an anchor ID: lowercase ASCII, words joined by
 * hyphens, German umlauts spelled out (ä → ae), filler words removed.
 * Free of WordPress so it can be tested with plain PHPUnit.
 *
 * @package RMD\HeadingAnchors
 */

declare(strict_types=1);

namespace RMD\HeadingAnchors\Domain;

final class Slugger {

	/** Longer IDs are cut at the last word boundary before this length. */
	public const MAX_LENGTH = 60;

	/** Articles, conjunctions and common prepositions (German and English), already transliterated. */
	public const DEFAULT_FILLER_WORDS = [
		'der',
		'die',
		'das',
		'den',
		'dem',
		'des',
		'ein',
		'eine',
		'einer',
		'eines',
		'einem',
		'einen',
		'und',
		'oder',
		'sowie',
		'bzw',
		'mit',
		'von',
		'vom',
		'zu',
		'zum',
		'zur',
		'im',
		'in',
		'ins',
		'am',
		'an',
		'auf',
		'aus',
		'bei',
		'beim',
		'fuer',
		'ueber',
		'unter',
		'nach',
		'vor',
		'durch',
		'um',
		'gegen',
		'ohne',
		'the',
		'a',
		'and',
		'or',
		'of',
		'to',
		'on',
		'at',
		'for',
		'with',
		'by',
		'from',
		'into',
	];

	/** Spelled out before any other transliteration, so "ä" never ends up as a plain "a". */
	private const GERMAN = [
		'ä' => 'ae',
		'ö' => 'oe',
		'ü' => 'ue',
		'Ä' => 'Ae',
		'Ö' => 'Oe',
		'Ü' => 'Ue',
		'ß' => 'ss',
		'ẞ' => 'SS',
	];

	/** @var array<string, true> */
	private array $filler_words;

	/**
	 * @param list<string> $filler_words Words to drop, in the form {@see self::words()} returns.
	 */
	public function __construct( array $filler_words = [] ) {
		$this->filler_words = array_fill_keys( $filler_words, true );
	}

	public function slugify( string $text ): string {
		$words = self::words( $text );

		if ( [] !== $this->filler_words ) {
			$kept = array_values( array_filter( $words, fn( string $word ): bool => ! isset( $this->filler_words[ $word ] ) ) );
			// A heading made only of filler words keeps them, otherwise it would get no ID.
			if ( [] !== $kept ) {
				$words = $kept;
			}
		}

		return self::truncate( implode( '-', $words ) );
	}

	/**
	 * Splits text into lowercase ASCII words. Entities are decoded, everything
	 * that is not a letter or digit separates words.
	 *
	 * @return list<string>
	 */
	public static function words( string $text ): array {
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = strtr( $text, self::GERMAN );
		$text = self::to_ascii( $text );
		$text = strtolower( $text );

		$words = preg_split( '/[^a-z0-9]+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		return false === $words ? [] : array_values( $words );
	}

	private static function to_ascii( string $text ): string {
		if ( function_exists( 'transliterator_transliterate' ) ) {
			$ascii = transliterator_transliterate( 'Any-Latin; Latin-ASCII', $text );
			if ( is_string( $ascii ) ) {
				return $ascii;
			}
		}

		// Without the intl extension: WordPress' own table, available whenever the plugin runs.
		if ( function_exists( 'remove_accents' ) ) {
			return remove_accents( $text );
		}
		return $text;
	}

	private static function truncate( string $slug ): string {
		if ( strlen( $slug ) <= self::MAX_LENGTH ) {
			return $slug;
		}
		$cut      = substr( $slug, 0, self::MAX_LENGTH + 1 );
		$boundary = strrpos( $cut, '-' );
		return false !== $boundary && $boundary > 0 ? substr( $cut, 0, $boundary ) : substr( $slug, 0, self::MAX_LENGTH );
	}
}
