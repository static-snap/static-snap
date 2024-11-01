<?php
/**
 * Get Urls Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Build;

use StaticSnap\Constants\Build_Type;
use StaticSnap\Database\Replacements_URLS_Database;
use StaticSnap\Deployment\Task;
use StaticSnap\Database\URLS_Database;
use StaticSnap\Filesystem\Filesystem;

/**
 * Get Urls Task class
 */
final class Prepare_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Preparing';




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

		$path = $this->deployment_process->get_environment()->get_build_path();

		$build_type = $this->deployment_process->get_build_type();

		if ( Build_Type::INCREMENTAL === $build_type ) {
			if ( ! is_dir( $path ) ) {
				throw new \Exception( __('Incremental build path does not exist.', 'static-snap') );
			}
			return true;
		}

		$filesystem = new Filesystem();

		if ( is_dir( $path ) ) {
			// empty the directory using wp_filesystem.
			$filesystem->delete( $path, true );
		}

		// recurisvely create the directory. Walk up the path and create directories as needed.
		$filesystem->create_directory_recursive( $path );

		URLS_Database::instance()->delete_all();
		URLS_Database::instance()->reset_auto_increment();
		Replacements_URLS_Database::instance()->delete_all();
		Replacements_URLS_Database::instance()->reset_auto_increment();

		return true;
	}
}
