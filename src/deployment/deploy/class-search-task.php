<?php
/**
 * Pagefind Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Deploy;

use StaticSnap\Database\URLS_Database;
use StaticSnap\Deployment\Task;
use StaticSnap\Application;
use StaticSnap\Config\Options;
use StaticSnap\Search\Search_Extension_Base;

/**
 * Pagefind Task class
 * Just to start the task
 */
final class Search_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Building Search Index';


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

		$search_options = Options::instance()->get(
			'search',
			array(
				'enabled' => false,
				'type'    => 'fuse-js',
			)
		);

		if ( ! $search_options['enabled'] ) {
			return true;
		}

		$search_extension = Application::instance()->get_extensions_by_type( 'search' )[ $search_options['type'] ];
		if ( ! $search_extension instanceof Search_Extension_Base ) {
			return true;
		}

		$build_path = $this->deployment_process->get_environment()->get_build_path();
		$urls       = URLS_Database::instance()->get_all( 'indexed' );

		while ( $urls ) {
			$indexed_posts = array();
			foreach ( $urls as $url ) {
				$url_type = $url->type;
				if ( 'post' !== $url_type ) {
					URLS_Database::instance()->set_indexed( $url->id );
					continue;
				}

				$related_id = $url->type_reference_id;

				$post = get_post( $related_id );
				if ( ! $post ) {
					URLS_Database::instance()->set_indexed( $url->id );
					continue;
				}
				$relative_url = wp_make_link_relative( $url->url );

				$to_index_posts = $search_extension->post_to_index( $post, $relative_url );
				foreach ( $to_index_posts as $to_index_post ) {
					$indexed_posts[] = $to_index_post;
				}

				URLS_Database::instance()->set_indexed( $url->id );
			}

			$search_extension->index_posts( $indexed_posts, $build_path );

			$urls = URLS_Database::instance()->get_all( 'indexed' );
		}
		return true;
	}
}
