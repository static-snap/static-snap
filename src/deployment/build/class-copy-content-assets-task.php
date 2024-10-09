<?php
/**
 * Get Urls Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Build;

use StaticSnap\Deployment\Task;
use StaticSnap\Database\URLS_Database;
use StaticSnap\Deployment\Assets;
use StaticSnap\Deployment\URL;

/**
 * Get Urls Task class
 */
final class Copy_Content_Assets_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Copying Content Assets';
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
		$urls_database = URLS_Database::instance();

		$urls = $urls_database->get_all( 'content_assets' );
		URL::add_default_filter();
		$environment = $this->deployment_process->get_environment();

		while ( $urls ) {

			foreach ( $urls as $url ) {

				// create a file from $url local_path.
				$urls_database->increase_retries( $url->id );

				$file     = new \SplFileInfo( $url->local_path );
				$finished = Assets::copy( $file, $url->url, $environment );
				if ( $finished ) {
					$urls_database->set_processed( $url->id, URLS_Database::PROCESSED_STATUS_SUCCESS );
					continue;
				}

				$urls_database->set_processed( $url->id, URLS_Database::PROCESSED_STATUS_FAILED );
			}

			$urls = $urls_database->get_all( 'content_assets' );
		}

		return true;
	}
}
