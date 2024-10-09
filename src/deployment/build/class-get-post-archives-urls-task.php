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
use StaticSnap\Deployment\Post_URL;
use StaticSnap\Deployment\URL;
use StaticSnap\Database\Posts_Database;

/**
 * Get Urls Task class
 */
final class Get_Post_Archives_Urls_Task extends Task {
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

		// Check if enable_author_pages is enabled.
		$enable_dates_pages = Options::instance()->get( 'build_options.enable_dates_pages', false );
		if ( ! $enable_dates_pages ) {
			return true;
		}

		$post_database = Posts_Database::instance();

		$dates = $post_database->get_all_archive_dates(
			array(
				'publish',
				'inherit',
			)
		);
		/**
		 *  Example return value of $dates
(
	[0] => Array
		(
			[post_type] => post
			[year] => 2023
			[month] => 3
			[posts] => 1
		)

	[1] => Array
		(
			[post_type] => post
			[year] => 2023
			[month] => 12
			[posts] => 1
		)

	[2] => Array
		(
			[post_type] => post
			[year] => 2024
			[month] => 1
			[posts] => 1
		)

	[3] => Array
		(
			[post_type] => post
			[year] => 2024
			[month] => 4
			[posts] => 12
		)

	[4] => Array
		(
			[post_type] => teams
			[year] => 2024
			[month] => 2
			[posts] => 1
		)

	[5] => Array
		(
			[post_type] => teams
			[year] => 2024
			[month] => 4
			[posts] => 1
		)

)
		 */

		$urls       = array();
		$post_types = array();
		foreach ( $dates as $date ) {
			$urls[]                           = new URL( get_month_link( $date['year'], $date['month'] ), 'Get_Post_Archives_Urls_Task::perform' );
			$post_types[ $date['post_type'] ] = ! empty( $post_types[ $date['post_type'] ] ) ? (int) $post_types[ $date['post_type'] ] + $date['posts'] : $date['posts'];

		}

		foreach ( $post_types as $post_type => $count_posts ) {

			$urls[] = new URL( get_post_type_archive_link( $post_type ), null, 'published', 'Get_Post_Archives_Urls_Task::perform' );

			// get pagination links.
			$pages = ceil( $count_posts / get_option( 'posts_per_page' ) );
			for ( $i = 1; $i <= $pages; $i++ ) {
				$urls[] = new URL( get_post_type_archive_link( $post_type ) . '/page/' . $i, null, 'published', 'Get_Post_Archives_Urls_Task::perform' );
			}
		}

		$urls_database = URLS_Database::instance();
		$urls          = apply_filters( Filters::BEFORE_SAVE_POST_ARCHIVES_URLS, $urls );
		$urls_database->insert_many( $urls );

		return true;
	}
}
