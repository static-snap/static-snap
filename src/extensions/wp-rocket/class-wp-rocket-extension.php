<?php
/**
 * WP Rocket Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\WP_Rocket;

/**
 * WP Rocket Extension
 */
final class WP_Rocket_Extension {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Disable WP Rocket caching.
		add_filter( 'do_rocket_generate_caching_files', '__return_false' );
	}
}
