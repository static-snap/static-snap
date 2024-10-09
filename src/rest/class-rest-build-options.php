<?php
/**
 * Rest_Build_Options
 *
 * @package StaticSnap
 */

namespace StaticSnap\Rest;

use StaticSnap\Constants\Filters;
use StaticSnap\Database\Posts_Database;
use WP_REST_Controller;

use StaticSnap\Environments\Github_Environment;

/**
 * REST Build Options class
 * This class is just for get some build option information
 */
final class Rest_Build_Options extends WP_REST_Controller {

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
	protected $rest_base = '/build-options';
	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/post-types',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'post_types' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/archive-post-types',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'archive_post_types' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
	}

	/**
	 * Get post types
	 *
	 * @return \WP_REST_Response
	 */
	public function post_types() {

		$post_types = Posts_Database::instance()->post_types();

		return rest_ensure_response( $post_types );
	}

	/**
	 * Get archive post types
	 *
	 * @return \WP_REST_Response
	 */
	public function archive_post_types() {
		$archive_post_types = Posts_Database::instance()->archive_post_types();

		return rest_ensure_response( $archive_post_types );
	}

	/**
	 * Check permissions
	 *
	 * @return bool
	 */
	public function permissions() {
		return current_user_can( 'manage_options' );
	}
}
