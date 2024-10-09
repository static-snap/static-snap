<?php
/**
 * Environment Interface
 * This interface is used to create new environment type
 *
 * @package StaticSnap
 */

namespace StaticSnap\Interfaces;

interface Environment_Type_Interface extends Extension_Interface {

	/**
	 * Needs Static Snap connect to be available
	 *
	 * @return boolean
	 */
	public function needs_connect(): bool;

	/**
	 * Needs zip file
	 *
	 * @return bool
	 */
	public function needs_zip(): bool;
	/**
	 * Get type
	 *
	 * @return string
	 */
	public function get_type(): string;



	/**
	 * This method is called when a build is published
	 *
	 * @param string $path path to the build.
	 * @return bool true if the build is published.
	 */
	public function on_publish( string $path ): bool;



	/**
	 * Get deployment tasks
	 * Add extra tasks to the deployment process just before the build is done
	 *
	 * @return array
	 */
	public function get_deployment_tasks(): array;
}
