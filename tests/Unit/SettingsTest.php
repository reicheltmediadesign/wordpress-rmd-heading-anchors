<?php
/**
 * @package RMD\HeadingAnchors
 */

declare(strict_types=1);

namespace RMD\HeadingAnchors\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RMD\HeadingAnchors\Domain\Settings;
use RMD\HeadingAnchors\Domain\Slugger;

final class SettingsTest extends TestCase {

	public function test_invalid_input_gives_defaults(): void {
		$this->assertSame( Settings::defaults(), Settings::parse( null ) );
		$this->assertSame( Settings::defaults(), Settings::parse( 'garbage' ) );
		$this->assertSame( Settings::defaults(), Settings::parse( [ 'levels' => 'h2', 'filler_words' => 'yes' ] ) );
	}

	public function test_form_values_are_converted(): void {
		$parsed = Settings::parse(
			[
				'levels'             => [ '0', '3', '2', '3', '9', 'x' ],
				'filler_words'       => '0',
				'extra_filler_words' => "Für, Ihre\nunsere  ",
				'other'              => 1,
			]
		);

		$this->assertSame(
			[
				'levels'             => [ 2, 3 ],
				'filler_words'       => false,
				'extra_filler_words' => [ 'fuer', 'ihre', 'unsere' ],
			],
			$parsed
		);
	}

	public function test_all_levels_can_be_switched_off(): void {
		$this->assertSame( [], Settings::parse( [ 'levels' => [ '0' ] ] )['levels'] );
	}

	public function test_filler_words(): void {
		$settings = Settings::parse( [ 'extra_filler_words' => [ 'ihre' ] ] );
		$this->assertContains( 'ihre', Settings::filler_words( $settings ) );
		$this->assertContains( 'und', Settings::filler_words( $settings ) );

		$settings['filler_words'] = false;
		$this->assertSame( [], Settings::filler_words( $settings ) );
	}

	public function test_extra_filler_words_are_limited(): void {
		$words = implode( ' ', array_map( static fn( int $i ): string => 'w' . $i, range( 1, 300 ) ) );
		$this->assertCount( Settings::EXTRA_FILLER_WORDS_MAX, Settings::parse( [ 'extra_filler_words' => $words ] )['extra_filler_words'] );
	}

	public function test_parse_is_idempotent(): void {
		$once = Settings::parse( [ 'levels' => [ 4, 2 ], 'extra_filler_words' => 'über' ] );
		$this->assertSame( $once, Settings::parse( $once ) );
	}

	public function test_default_filler_words_are_normalised(): void {
		foreach ( Slugger::DEFAULT_FILLER_WORDS as $word ) {
			$this->assertSame( [ $word ], Slugger::words( $word ) );
		}
	}
}
