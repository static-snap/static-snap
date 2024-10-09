<?php
/**
 * Local Dev
 *
 * @package StaticSnap
 */

namespace StaticSnap\Local_Dev;

use StaticSnap\Constants\Filters;
use StaticSnap\Traits\Singleton;

/**
 * Class to manage local dev variables.
 */
final class Local_Dev {
	use Singleton;

	/**
	 * Local Dev Interface
	 *
	 * @var Local_Dev_Interface
	 */
	private $local_dev = null;

	/**
	 * Init
	 */
	private function init() {
		if ( file_exists( __DIR__ . '/class-local-dev-local.php' ) ) {
			require_once __DIR__ . '/class-local-dev-local.php';
			// check if LocalDevLocal class exists and implement the interface.
			if ( class_exists( 'StaticSnap\Local_Dev\Local_Dev_Local' ) ) {
				$dev_local_implements = class_implements( 'StaticSnap\Local_Dev\Local_Dev_Local' );
				if ( in_array( 'StaticSnap\Local_Dev\Local_Dev_Interface', $dev_local_implements, true ) ) {
					// phpcs:ignore
					$this->local_dev = new Local_Dev_Local();

					add_filter( Filters::API_URL, array( $this->local_dev, 'get_static_snap_api_url' ), 10, 2 );
					add_filter( Filters::WEBSITE_URL, array( $this->local_dev, 'get_static_snap_website_url' ), 10, 2 );
				}
			}
		}
	}

	/**
	 * Is using local dev
	 *
	 * @return bool
	 */
	public function is_using_local_dev() {
		return null !== $this->local_dev;
	}
}
