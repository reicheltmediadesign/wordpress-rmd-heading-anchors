<?php
/**
 * One-time data changes after a plugin update. The installed version is kept
 * in an option; add steps here guarded by a version comparison.
 *
 * @package RMD\HeadingAnchors
 */

namespace RMD\HeadingAnchors;

defined( 'ABSPATH' ) || exit;

final class Upgrade {

	public const OPTION = 'rmd_ha_version';

	public static function run(): void {
		$installed = (string) get_option( self::OPTION, '' );
		if ( '' !== $installed && version_compare( $installed, RMD_HA_VERSION, '>=' ) ) {
			return;
		}

		update_option( self::OPTION, RMD_HA_VERSION, true );
	}
}
