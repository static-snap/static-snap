<?php
/**
 * Phase Interface
 *
 * @package StaticSnap
 */

namespace StaticSnap\Interfaces;

/**
 * Phase Interface
 */

interface Deployment_Interface {
	/**
	 * Run the Phase in the admin dashboard.
	 *
	 * @param Environment_Interface $environment Environment.
	 * @return bool
	 */
	public function run( Environment_Interface $environment ): bool;

	/**
	 * Run the Phase in the cli command line.
	 *
	 * @param Environment_Interface $environment Environment.
	 *
	 * @return bool
	 */
	public function run_cli( Environment_Interface $environment ): bool;

	/**
	 * Cancel the Phase.
	 *
	 * @return void
	 */
	public function cancel(): void;

	/**
	 * Deployment failed
	 *
	 * @param mixed $error Error.
	 *
	 * @return void
	 */
	public function failed( $error ): void;



	/**
	 * Pause the Phase.
	 *
	 * @return void
	 */
	public function pause(): void;

	/**
	 * Get environment
	 *
	 * @return Environment_Interface
	 */
	public function get_environment(): Environment_Interface;
}
