<?php
/**
 * Get Urls Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Build;

use StaticSnap\Deployment\Task;
use StaticSnap\Constants\Actions;
use StaticSnap\Constants\Build_Type;
use StaticSnap\Constants\Filters;
use StaticSnap\Database\URLS_Database;
use StaticSnap\Deployment\Post_URL;
use StaticSnap\Deployment\URL;
use StaticSnap\Database\Posts_Database;
use StaticSnap\Deployment\Fixed_URL;

/**
 * Get Urls Task class
 */
final class Get_Posts_Urls_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Getting Posts Urls';
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
		$this->deployment_process->get_environment();

		$build_type = $this->deployment_process->get_build_type();
		$extra_args = array();
		$reset_process_field = false;
		if ( Build_Type::INCREMENTAL === $build_type ) {
			$reset_process_field = true;
			$last_build_datetime = $this->deployment_process->get_last_build_date()->format( 'Y-m-d H:i:s' );
			$extra_args['date_query'] = array(
				'column' => 'post_modified_gmt',
				'after' => $last_build_datetime,
			);
		}

		$post_database = Posts_Database::instance();

		$posts = $post_database->get_all( array( 'publish', 'inherit' ), $extra_args );


		$urls = array();

		$home_page_type = get_option( 'show_on_front' ) ?? 'posts';

		if ( 'posts' === $home_page_type ) {
			$home = get_post( get_option( 'page_for_posts' ) );
			if ( $home ) {
				$urls[] = new Post_URL( $home, 'Get_Posts_Urls_Task::perform' );
			}
		}

		if ( 'page' === $home_page_type ) {
			$home = get_post( get_option( 'page_on_front' ) );
			if ( $home ) {
				$urls[] = new Post_URL( $home, 'Get_Posts_Urls_Task::perform' );
			}
		}

		if ( empty( $urls ) ) {
			// add home url as default.
			$urls[] = new URL( home_url( '/' ), gmdate( 'Y-m-d H:i:s', strtotime( 'now' ) ), 'publish', 'Get_Posts_Urls_Task::perform' );

		}

		// add favicon.ico, 404.html and robots.txt urls to the list.
		$urls[] = new Fixed_URL( home_url( '/favicon.ico' ), gmdate( 'Y-m-d H:i:s', strtotime( 'now' ) ), 'publish', 'Get_Posts_Urls_Task::perform' );
		$urls[] = new Fixed_URL( home_url( '/404.html' ), gmdate( 'Y-m-d H:i:s', strtotime( 'now' ) ), 'publish', 'Get_Posts_Urls_Task::perform' );
		$urls[] = new Fixed_URL( home_url( '/robots.txt' ), gmdate( 'Y-m-d H:i:s', strtotime( 'now' ) ), 'publish', 'Get_Posts_Urls_Task::perform' );

		foreach ( $posts as $post ) {

			do_action( Actions::BEFORE_CREATE_POST_URL, $post );
			$url = new Post_URL( $post, 'Get_Posts_Urls_Task::perform' );

			if ( $url->is_valid() ) {

				$urls[] = $url;
			}
			do_action( Actions::AFTER_CREATE_POST_URL, $url );
		}

		$urls_database = URLS_Database::instance();
		$urls          = apply_filters( Filters::BEFORE_SAVE_POST_URLS, $urls );
		$urls_database->insert_many( $urls , $reset_process_field );

		return true;
	}
}
