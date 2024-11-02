<?php
/**
 * Get Urls Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Build;

use StaticSnap\Deployment\Task;
use StaticSnap\Constants\Actions;
use StaticSnap\Database\URLS_Database;
use StaticSnap\Deployment\URL;



/**
 * Get Urls Task class
 */
final class Fetch_Posts_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Fetching Posts';
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

		$limit = 20;

		// get current offset.
		$urls = $urls_database->get_all( 'posts', 'all', $limit );

		$environment = $this->deployment_process->get_environment();

		URL::add_default_filter();

		while ( $urls ) {

			foreach ( $urls as $url ) {

				do_action( Actions::BEFORE_FETCH_POST_URL, $url );
				// create a file from $url local_path.
				$urls_database->increase_retries( $url->id );

				$result                 = URL::save_url( $url, $environment );

				$saved                  = $result['saved'];
				$local_path_destination = $result['local_path_destination'];
				do_action(
					Actions::AFTER_FETCH_POST_URL,
					array(
						'url'   => $url,
						'saved' => $saved,
					)
				);
				if ( $saved ) {
					$urls_database->set_processed( $url->id, URLS_Database::PROCESSED_STATUS_SUCCESS, $local_path_destination );
					continue;
				}

				$urls_database->set_processed( $url->id, URLS_Database::PROCESSED_STATUS_FAILED );
			}

			$urls = $urls_database->get_all( 'posts', 'all', $limit );

		}

		return true;
	}
}
