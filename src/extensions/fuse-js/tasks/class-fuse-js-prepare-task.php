<?php
/**
 * Pagefind Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Search\FuseJS\Tasks;

use StaticSnap\Deployment\Task;
use StaticSnap\Extensions\Search\FuseJS\Fuse_JS;

/**
 * Pagefind Task class
 * Just to start the task
 */
final class Fuse_JS_Prepare_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Preparing Fuse.js Index';


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

		$build_path = $this->deployment_process->get_environment()->get_build_path();
		$file_path  = Fuse_JS::get_search_file_path( $build_path );

		Fuse_JS::prepare_index( $file_path );

		return true;
	}
}
