<?php
/**
 * Get Urls Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Deploy;

use StaticSnap\Deployment\Task;
use StaticSnap\Database\Deployment_History_Database;

/**
 * Get Urls Task class
 */
final class Done_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Done';
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
	public function perform(): bool {
		Deployment_History_Database::instance()->end_history(
			Deployment_History_Database::DONE,
			null,
			array(
				'current_task'             => self::class,
				'current_task_description' => $this->description,
				'percentage'               => 100,
			)
		);
		return true;
	}
}
