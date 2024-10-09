<?php
/**
 * Local Dev Interface
 *
 * @package StaticSnap
 */

namespace StaticSnap\Local_Dev;

/**
 * Interface for local dev
 */
interface Local_Dev_Interface {
	/**
	 * Get the local dev url
	 *
	 * @param string $path Path to append to the local dev url.
	 * @param string $scope Scope of the url.
	 *
	 * @return string
	 */
	public function get_static_snap_website_url( string $path, $scope = 'backend' ): string;

	/**
	 * Get the local dev api url
	 *
	 * @param string $path Path to append to the local dev url.
	 * @param string $scope Scope of the url.
	 *
	 * @return string
	 */
	public function get_static_snap_api_url( string $path, $scope = 'backend' ): string;
}
