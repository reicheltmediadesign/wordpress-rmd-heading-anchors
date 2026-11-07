<?php
/**
 * "Anchor" panel for heading blocks in the block editor. It shows the anchor
 * the heading gets automatically and lets editors fix it (or enter their own)
 * in the block's "anchor" attribute. That attribute is saved as the heading's
 * id in the content, so it no longer changes with the heading text.
 *
 * The automatic anchors come from a REST route that runs the same code as the
 * output filter, so editor and front end never disagree.
 *
 * @package RMD\HeadingAnchors
 */

namespace RMD\HeadingAnchors\Modules;

use RMD\HeadingAnchors\Settings;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

final class EditorPanel {

	public const HANDLE    = 'rmd-ha-editor-panel';
	public const NAMESPACE = 'rmd-heading-anchors/v1';

	/** Upper bound for headings per request; real posts stay far below. */
	private const MAX_HEADINGS = 1000;

	public static function init(): void {
		add_action( 'enqueue_block_editor_assets', [ self::class, 'enqueue' ] );
		add_action( 'rest_api_init', [ self::class, 'register_routes' ] );
	}

	public static function enqueue(): void {
		$path = 'assets/js/editor-panel.js';
		wp_enqueue_script(
			self::HANDLE,
			RMD_HA_URL . $path,
			[ 'wp-api-fetch', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-data', 'wp-element', 'wp-hooks', 'wp-i18n' ],
			(string) filemtime( RMD_HA_DIR . $path ),
			true
		);
		wp_add_inline_script(
			self::HANDLE,
			'window.rmdHaEditor = ' . wp_json_encode( [ 'levels' => Settings::get()['levels'] ] ) . ';',
			'before'
		);
		wp_set_script_translations( self::HANDLE, 'rmd-heading-anchors', RMD_HA_DIR . 'languages' );
	}

	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/anchors',
			[
				'methods'             => 'POST',
				'callback'            => [ self::class, 'anchors' ],
				'permission_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
				'args'                => [
					'headings' => [
						'type'     => 'array',
						'required' => true,
						'maxItems' => self::MAX_HEADINGS,
						'items'    => [
							'type'       => 'object',
							'properties' => [
								'level'   => [
									'type'    => 'integer',
									'minimum' => 1,
									'maximum' => 6,
								],
								'content' => [ 'type' => 'string' ],
								'anchor'  => [ 'type' => 'string' ],
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Returns the ID every heading gets on output, in the order given.
	 *
	 * @param WP_REST_Request $request Request with "headings": list of {level, content, anchor}.
	 */
	public static function anchors( WP_REST_Request $request ): WP_REST_Response {
		$html = '';
		foreach ( (array) $request->get_param( 'headings' ) as $heading ) {
			$level   = (int) ( $heading['level'] ?? 2 );
			$content = (string) ( $heading['content'] ?? '' );
			$anchor  = (string) ( $heading['anchor'] ?? '' );

			// Tags in the text are irrelevant for the ID; removing them keeps a stray "</h2>" from splitting the fragment.
			$html .= sprintf(
				'<h%1$d%2$s>%3$s</h%1$d>',
				$level,
				'' === $anchor ? '' : ' id="' . esc_attr( $anchor ) . '"',
				wp_strip_all_tags( $content )
			);
		}

		return new WP_REST_Response( [ 'ids' => Settings::anchors()->ids( $html ) ] );
	}
}
