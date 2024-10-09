<?php
/**
 * Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Interfaces;

use StaticSnap\Deployment\Deployment_Process;

/**
 * Task class
 */
interface Task_Interface {


	/**
	 * Constructor
	 *
	 * @param Deployment_Process $deployment_process Deployment process.
	 */
	public function __construct( Deployment_Process $deployment_process );
	/**
	 * Perform task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @return bool
	 */
	public function perform(): bool;

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function get_description();
}
