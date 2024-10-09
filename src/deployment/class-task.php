<?php
/**
 * Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Interfaces\Task_Interface;

/**
 * Task class
 */
abstract class Task implements Task_Interface {

	/**
	 * Deployment process
	 *
	 * @var Deployment_Process
	 */
	protected $deployment_process = null;


	/**
	 * Name
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Constructor
	 *
	 * @param Deployment_Process $deployment_process Deployment process.
	 */
	public function __construct( Deployment_Process $deployment_process ) {
		$this->deployment_process = $deployment_process;
	}
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
	abstract public function perform(): bool;

	/**
	 * To string
	 */
	public function __toString() {
		return static::class;
	}
	/**
	 * Get description
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description ?? static::class;
	}
}
