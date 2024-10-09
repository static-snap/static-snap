<?php
/**
 * Interface  URL
 *
 * @package StaticSnap
 */

namespace StaticSnap\Interfaces;

/**
 * Interface URL defines the methods that a URL object should implement.
 */
interface Deployment_URL_Interface {

	/**
	 * Get  priority
	 *
	 * @return int
	 */
	public function get_priority(): int;

	/**
	 * Get source
	 * Source is where the url is generated from.
	 */
	public function get_source(): string;

	/**
	 * Get  URL
	 *
	 * @return string
	 */
	public function get_url(): string;



	/**
	 * Get url hash
	 *
	 * @return string
	 */
	public function get_url_hash(): string;

	/**
	 * Get local path
	 *
	 * @return string | null
	 */
	public function get_local_path();

	/**
	 * Get last modified
	 *
	 * @return string
	 */
	public function get_last_modified(): string;

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function get_status(): string;

	/**
	 * To array
	 *
	 * @return array
	 */
	public function to_array(): array;

	/**
	 *
	 * Is valid url
	 * Valid url will be saved to the database by the deployment task.
	 * Invalid urls will be ignored.
	 *
	 * @return bool
	 */
	public function is_valid(): bool;
}
