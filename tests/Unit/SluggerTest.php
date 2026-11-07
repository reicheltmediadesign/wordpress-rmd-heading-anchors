<?php
/**
 * @package RMD\HeadingAnchors
 */

declare(strict_types=1);

namespace RMD\HeadingAnchors\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RMD\HeadingAnchors\Domain\Slugger;

final class SluggerTest extends TestCase {

	private function slugger(): Slugger {
		return new Slugger( Slugger::DEFAULT_FILLER_WORDS );
	}

	public function test_umlauts_are_spelled_out(): void {
		$this->assertSame( 'oeffnungszeiten-ueber-ostern', ( new Slugger() )->slugify( 'Öffnungszeiten über Ostern' ) );
		$this->assertSame( 'strasse-aerger', ( new Slugger() )->slugify( 'Straße & Ärger' ) );
	}

	public function test_other_accents_are_removed(): void {
		$this->assertSame( 'cafe-creme-smorrebrod', ( new Slugger() )->slugify( 'Café Crème – Smørrebrød' ) );
	}

	public function test_special_characters_become_single_hyphens(): void {
		$this->assertSame( 'preise-2026-faq', ( new Slugger() )->slugify( '  Preise 2026 / FAQ?!  ' ) );
		$this->assertSame( 'a-b', ( new Slugger() )->slugify( 'A&nbsp;&amp;&nbsp;B' ) );
	}

	public function test_filler_words_are_removed(): void {
		$this->assertSame( 'haeufige-fragen-anfahrt', $this->slugger()->slugify( 'Häufige Fragen zur Anfahrt' ) );
		$this->assertSame( 'leistungen-preise', $this->slugger()->slugify( 'Die Leistungen und die Preise' ) );
		$this->assertSame( 'about-us-our-services', $this->slugger()->slugify( 'About us & our services' ) );
		$this->assertSame( 'oeffnungszeiten-ostern', $this->slugger()->slugify( 'Öffnungszeiten für Ostern' ) );
	}

	public function test_heading_of_filler_words_only_keeps_them(): void {
		$this->assertSame( 'und-oder', $this->slugger()->slugify( 'Und / oder' ) );
	}

	public function test_empty_text_gives_empty_slug(): void {
		$this->assertSame( '', $this->slugger()->slugify( '' ) );
		$this->assertSame( '', $this->slugger()->slugify( '★ ?! ★' ) );
	}

	public function test_long_slugs_are_cut_at_a_word_boundary(): void {
		$slug = ( new Slugger() )->slugify( str_repeat( 'abcdefghi ', 10 ) );
		$this->assertLessThanOrEqual( Slugger::MAX_LENGTH, strlen( $slug ) );
		$this->assertStringEndsWith( 'abcdefghi', $slug );
	}

	public function test_words_are_normalised(): void {
		$this->assertSame( [ 'fuer', 'ueber', 'strasse' ], Slugger::words( 'Für, ÜBER; Straße' ) );
	}
}
