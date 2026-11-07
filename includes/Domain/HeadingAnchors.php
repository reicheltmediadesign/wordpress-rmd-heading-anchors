<?php
/**
 * Adds an id attribute to every heading of an HTML fragment that has none.
 * IDs are unique within the fragment (existing IDs are reserved, repeats get
 * "-2", "-3", …) and depend only on the fragment itself, so the same post
 * always gets the same anchors. Free of WordPress so it can be tested with
 * plain PHPUnit.
 *
 * @package RMD\HeadingAnchors
 */

declare(strict_types=1);

namespace RMD\HeadingAnchors\Domain;

final class HeadingAnchors {

	private const HEADING = '/<h([1-6])(\s[^>]*)?>(.*?)<\/h\1\s*>/is';

	private const ID_ATTRIBUTE = '/\sid\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i';

	/**
	 * @param list<int> $levels Heading levels (1–6) that get an ID.
	 */
	public function __construct(
		private Slugger $slugger,
		private array $levels
	) {}

	public function apply( string $html ): string {
		if ( [] === $this->levels || false === stripos( $html, '<h' ) ) {
			return $html;
		}

		$used = self::existing_ids( $html );

		$result = preg_replace_callback(
			self::HEADING,
			function ( array $heading ) use ( &$used ): string {
				$attributes = $heading[2] ?? '';
				if ( ! in_array( (int) $heading[1], $this->levels, true ) || 1 === preg_match( self::ID_ATTRIBUTE, $attributes ) ) {
					return $heading[0];
				}

				$slug = $this->slugger->slugify( strip_tags( $heading[3] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- Domain stays free of WordPress; only text is needed.
				if ( '' === $slug ) {
					return $heading[0];
				}

				$id = $slug;
				for ( $n = 2; isset( $used[ $id ] ); $n++ ) {
					$id = $slug . '-' . $n;
				}
				$used[ $id ] = true;

				return '<h' . $heading[1] . ' id="' . $id . '"' . $attributes . '>' . $heading[3] . '</h' . $heading[1] . '>';
			},
			$html
		);

		return $result ?? $html;
	}

	/**
	 * The ID every heading of the fragment has after {@see self::apply()}, in
	 * document order; '' for headings that get none.
	 *
	 * @return list<string>
	 */
	public function ids( string $html ): array {
		if ( ! preg_match_all( self::HEADING, $this->apply( $html ), $headings, PREG_SET_ORDER ) ) {
			return [];
		}

		$ids = [];
		foreach ( $headings as $heading ) {
			$ids[] = 1 === preg_match( self::ID_ATTRIBUTE, $heading[2] ?? '', $id_match )
				? ( $id_match[1] ?? '' ) . ( $id_match[2] ?? '' ) . ( $id_match[3] ?? '' )
				: '';
		}
		return $ids;
	}

	/**
	 * @return array<string, true>
	 */
	private static function existing_ids( string $html ): array {
		$ids = [];
		if ( preg_match_all( self::ID_ATTRIBUTE, $html, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $id_match ) {
				$id = ( $id_match[1] ?? '' ) . ( $id_match[2] ?? '' ) . ( $id_match[3] ?? '' );
				if ( '' !== $id ) {
					$ids[ $id ] = true;
				}
			}
		}
		return $ids;
	}
}
