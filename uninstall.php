<?php
/**
 * Removes the plugin's options on every site. Anchor IDs are added on output
 * only, so post content stays untouched.
 *
 * @package RMD\HeadingAnchors
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Deletes the options of the current site.
 */
function rmd_ha_uninstall_site(): void {
	foreach ( [ 'rmd_ha_settings', 'rmd_ha_version', 'external_updates-rmd-heading-anchors' ] as $rmd_ha_option ) {
		delete_option( $rmd_ha_option );
	}
}

if ( is_multisite() ) {
	$rmd_ha_site_ids = get_sites(
		[
			'fields' => 'ids',
			'number' => 0,
		]
	);
	foreach ( $rmd_ha_site_ids as $rmd_ha_site_id ) {
		switch_to_blog( (int) $rmd_ha_site_id );
		rmd_ha_uninstall_site();
		restore_current_blog();
	}
} else {
	rmd_ha_uninstall_site();
}
