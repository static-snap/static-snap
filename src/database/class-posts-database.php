<?php
/**
 * Get posts
 *
 * @package StaticSnap
 */

namespace StaticSnap\Database;

use StaticSnap\Constants\Filters;
use StaticSnap\Traits\Singleton;

/**
 * Get posts class
 */
final class Posts_Database {
	use Singleton;


	/**
	 * Get all post types
	 *
	 * @return array
	 */
	public function post_types() {
		return apply_filters( Filters::POST_TYPES, get_post_types( array( 'public' => true ), 'names', 'and' ) );
	}

	/**
	 * Count posts
	 *
	 * @param array $post_statuses Post statuses.
	 * @return int
	 */
	public function count( $post_statuses = array( 'publish', 'inherit' ) ): int {
		$post_types = $this->post_types();

		$args      = array(
			'post_type'        => $post_types,
			'posts_per_page'   => -1,
			'post_status'      => $post_statuses,
			'suppress_filters' => true,
		);
		$the_query = new \WP_Query( $args );

		return $the_query->found_posts;
	}

	/**
	 * Get posts
	 *
	 * @param array $post_statuses Post statuses.
	 * @return array
	 */
	public function get_all( $post_statuses = array( 'publish', 'inherit' ) ): array {

		$post_types = $this->post_types();

		$args      = array(
			'post_type'        => $post_types,
			'posts_per_page'   => -1,
			'post_status'      => $post_statuses,
			'suppress_filters' => true,
		);
		$the_query = new \WP_Query( $args );

		return $the_query->posts;
	}



	/**
	 * Get all post types that have archive pages.
	 *
	 * @return array
	 */
	public function archive_post_types() {
		return apply_filters(
			Filters::ARCHIVE_POST_TYPES,
			array_merge(
				array( 'post' ),
				get_post_types(
					array(
						'has_archive' => true,
						'public'      => true,
					),
					'names',
					'and'
				)
			)
		);
	}

	/**
	 * Get all dates grouped by post type
	 *
	 * @param array $post_statuses Post statuses.
	 *
	 * @return array
	 */
	public function get_all_archive_dates( $post_statuses = array( 'publish', 'inherit' ) ): array {
		global $wpdb;
		// get all post types that have archive pages.
		$post_types = $this->archive_post_types();
		$query      = sprintf(
			"SELECT post_type, YEAR(post_date) AS year, MONTH(post_date) AS month, count(ID) as posts FROM $wpdb->posts WHERE post_type IN (%s) AND post_status IN (%s) GROUP BY post_type, YEAR(post_date), MONTH(post_date) ORDER BY post_type, year, month",
			Table::prepare_array( $post_types ),
			Table::prepare_array( $post_statuses )
		);
		// phpcs:ignore
		$dates      = $wpdb->get_results(
			// phpcs:ignore
			$query,
			ARRAY_A
		);
		return $dates;
	}
}
