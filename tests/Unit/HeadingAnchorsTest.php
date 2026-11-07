<?php
/**
 * @package RMD\HeadingAnchors
 */

declare(strict_types=1);

namespace RMD\HeadingAnchors\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RMD\HeadingAnchors\Domain\HeadingAnchors;
use RMD\HeadingAnchors\Domain\Slugger;

final class HeadingAnchorsTest extends TestCase {

	private function anchors( array $levels = [ 2, 3, 4, 5, 6 ] ): HeadingAnchors {
		return new HeadingAnchors( new Slugger( Slugger::DEFAULT_FILLER_WORDS ), $levels );
	}

	public function test_adds_ids_and_keeps_attributes(): void {
		$this->assertSame(
			'<h2 id="unsere-leistungen" class="wp-block-heading">Unsere <em>Leistungen</em></h2><p>Text</p>',
			$this->anchors()->apply( '<h2 class="wp-block-heading">Unsere <em>Leistungen</em></h2><p>Text</p>' )
		);
	}

	public function test_existing_ids_are_kept(): void {
		$html = '<h2 id="eigener-anker">Kontakt</h2><h3 class="x" ID=\'y\'>Z</h3>';
		$this->assertSame( $html, $this->anchors()->apply( $html ) );
	}

	public function test_duplicates_get_a_number(): void {
		$this->assertSame(
			'<h2 id="kontakt">Kontakt</h2><h3 id="kontakt-2">Kontakt</h3><h3 id="kontakt-3">Kontakt</h3>',
			$this->anchors()->apply( '<h2>Kontakt</h2><h3>Kontakt</h3><h3>Kontakt</h3>' )
		);
		$this->assertSame(
			'<div id="kontakt"></div><h2 id="kontakt-2">Kontakt</h2>',
			$this->anchors()->apply( '<div id="kontakt"></div><h2>Kontakt</h2>' )
		);
	}

	public function test_only_selected_levels(): void {
		$this->assertSame(
			'<h1>Titel</h1><h2 id="a">A</h2><h5>B</h5>',
			$this->anchors( [ 2, 3 ] )->apply( '<h1>Titel</h1><h2>A</h2><h5>B</h5>' )
		);
		$html = '<h2>A</h2>';
		$this->assertSame( $html, $this->anchors( [] )->apply( $html ) );
	}

	public function test_headings_without_usable_text_are_left_alone(): void {
		$html = '<h2><img src="x.png" alt=""></h2><h2>★</h2>';
		$this->assertSame( $html, $this->anchors()->apply( $html ) );
	}

	public function test_does_not_touch_similar_tags(): void {
		$html = '<header data-id="x"><hr><h7>No</h7></header>';
		$this->assertSame( $html, $this->anchors()->apply( $html ) );
	}

	public function test_multiline_headings(): void {
		$this->assertSame(
			"<h2 id=\"zeile-eins-zeile-zwei\">\n\tZeile eins<br>\n\tZeile zwei\n</h2>",
			$this->anchors()->apply( "<h2>\n\tZeile eins<br>\n\tZeile zwei\n</h2>" )
		);
	}

	public function test_ids_in_document_order(): void {
		$this->assertSame(
			[ '', 'kontakt-2', 'kontakt', 'anfahrt', '' ],
			$this->anchors()->ids( '<h1>Titel</h1><h2>Kontakt</h2><h2 id="kontakt">Alt</h2><h3>Die Anfahrt</h3><h2>★</h2>' )
		);
		$this->assertSame( [], $this->anchors()->ids( '<p>Text</p>' ) );
	}

	public function test_result_is_stable(): void {
		$once = $this->anchors()->apply( '<h2>Kontakt</h2><h2>Kontakt</h2>' );
		$this->assertSame( '<h2 id="kontakt">Kontakt</h2><h2 id="kontakt-2">Kontakt</h2>', $once );
		$this->assertSame( $once, $this->anchors()->apply( $once ) );
	}
}
