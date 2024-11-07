<?php
/**
 * Class Fixed URL
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

/**
 * Fixed URL class
 * This class is used to create a fixed URL, for example /favicon.ico
 */
final class Fixed_URL extends URL {


	/**
	 * Constructor
	 *
	 * @param string $url URL.
	 * @param string $last_modified Last modified.
	 * @param string $status Status.
	 * @param string $source Source.
	 */
	public function __construct( $url, $last_modified = null, $status = 'published', $source = 'Fixed_URL::class' ) {
		parent::__construct( $url, $last_modified, $status, $source );
		$this->set_priority( 11 );
	}
	/**
	 * Get status
	 *
	 * @return string
	 */
	public function get_status(): string {
		return 'published';
	}

	/**
	 * Get url type
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'fixed_url';
	}
}
