<?php
/**
 * Start Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Build;

use StaticSnap\Deployment\Task;

/**
 * Start Task class
 * Just to start the task
 */
final class Start_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Starting';
	/**
	 * Perform task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @return mixed
	 */
	public function perform(): bool {
		return true;
	}
}
