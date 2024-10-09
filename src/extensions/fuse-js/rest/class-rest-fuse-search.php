<?php
/**
 * REST Environments
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Search\FuseJS\Rest;

use StaticSnap\Database\Posts_Database;
use StaticSnap\Application;
use WP_REST_Controller;
use WP_REST_Server;

/**
 * REST Environments class
 * This class expose all Environment_Database methods to the REST API
 */
final class Rest_FUSE_Search extends WP_REST_Controller {

	/**
	 * Namespace
	 *
	 * @var string
	 */
	protected $namespace = 'static-snap/v1';
	/**
	 * Rest base
	 *
	 * @var string
	 */
	protected $rest_base = '/fuse-search';
	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/index',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_index' ),
					'permission_callback' => array( $this, 'public_permissions' ),
				),

			)
		);
	}


	/**
	 * Get environments
	 *
	 * @return \WP_REST_Response
	 */
	public function get_index() {

		$posts            = Posts_Database::instance()->get_all();
		$indexed_posts    = array();
		$search_extension = Application::instance()->get_extensions_by_type( 'search' )['fuse-js'];
		foreach ( $posts as $post ) {
			$to_index_posts = $search_extension->post_to_index( $post );
			foreach ( $to_index_posts as $to_index_post ) {
				$indexed_posts[] = $to_index_post;
			}
		}

		return rest_ensure_response( $indexed_posts );
	}

	/**
	 * Public permissions
	 *
	 * @return bool
	 */
	public function public_permissions() {
		return true;
	}
}
