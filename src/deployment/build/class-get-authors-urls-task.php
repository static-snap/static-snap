<?php
/**
 * Get Urls Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Build;

use StaticSnap\Config\Options;
use StaticSnap\Deployment\Task;
use StaticSnap\Constants\Actions;
use StaticSnap\Constants\Filters;
use StaticSnap\Database\URLS_Database;
use StaticSnap\Deployment\Author_URL;




/**
 * Get Urls Task class
 */
final class Get_Authors_Urls_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Getting Authors Urls';
	/**
	 * Perform task
	 *
	 * @return bool
	 */
	public function perform(): bool {

		// Check if enable_author_pages is enabled.
		$enable_author_pages = Options::instance()->get( 'build_options.enable_author_pages', false );
		if ( ! $enable_author_pages ) {
			return true;
		}

		$authors = get_users();
		$urls    = array();
		foreach ( $authors as $author ) {
			$url = new Author_URL( $author, 'Get_Authors_Urls_Task::perform' );
			if ( $url->is_valid() ) {
				$urls[] = $url;
			}
		}

		$urls_database = URLS_Database::instance();
		$urls          = apply_filters( Filters::BEFORE_SAVE_AUTHORS_URLS, $urls );
		$urls_database->insert_many( $urls );

		return true;
	}
}
