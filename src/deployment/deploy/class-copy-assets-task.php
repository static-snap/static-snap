<?php
/**
 * Get Urls Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Deploy;

use StaticSnap\Deployment\Task;
use StaticSnap\Database\URLS_Database;
use StaticSnap\Deployment\Assets;
use StaticSnap\Deployment\URL;

/**
 * Get Urls Task class
 */
final class Copy_Assets_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Copying Assets';
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

		$database = URLS_Database::instance();
		// get current offset.
		$limit = 20;
		$urls  = $database->get_all( 'assets', 'all', $limit );

		$environment = $this->deployment_process->get_environment();
		URL::add_default_filter();

		while ( $urls ) {

			foreach ( $urls as $url ) {

				// create a file from $url local_path.
				$database->increase_retries( $url->id );
				$file   = new \SplFileInfo( $url->local_path );
				$result = Assets::copy( $file, $url, $environment );
				if ( $result['saved'] ) {
					$database->set_processed( $url->id, URLS_Database::PROCESSED_STATUS_SUCCESS, $result['local_path_destination'] );
					continue;
				}

				$database->set_processed( $url->id, URLS_Database::PROCESSED_STATUS_FAILED );
			}

			$urls = $database->get_all( 'assets', 'all', $limit );
		}

		return true;
	}
}
