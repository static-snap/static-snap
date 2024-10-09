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
use StaticSnap\Deployment\Term_URL;
use StaticSnap\Deployment\URL;



/**
 * Get Urls Task class
 */
final class Get_Terms_Urls_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Getting Terms Urls';
	/**
	 * Perform task
	 *
	 * @return bool
	 */
	public function perform(): bool {

		$enable_terms_pages = Options::instance()->get( 'build_options.enable_terms_pages', false );
		if ( ! $enable_terms_pages ) {
			return true;
		}

		$taxonomies = get_taxonomies( array( 'public' => true ) );
		$urls       = array();
		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'get'        => 'all',
				)
			);
			foreach ( $terms as $term ) {

				do_action( Actions::BEFORE_CREATE_TERM_URL, $term );
				$url = new Term_URL( $term, 'Get_Terms_Urls_Task::perform' );

				if ( $url->is_valid() ) {
					$urls[] = $url;
				}

				// count the number of posts in the term.
				$pages = ceil( $term->count / get_option( 'posts_per_page' ) );
				for ( $i = 1; $i <= $pages; $i++ ) {
					$urls[] = new URL( $url->get_url() . '/page/' . $i, 'Get_Terms_Urls_Task::perform' );
				}

				do_action( Actions::AFTER_CREATE_TERM_URL, $url );
			}
		}

		$database = URLS_Database::instance();

		$urls = apply_filters( Filters::BEFORE_SAVE_TERM_URLS, $urls );
		$database->insert_many( $urls );

		return true;
	}
}
