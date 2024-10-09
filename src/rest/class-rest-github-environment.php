<?php
/**
 * Github_Environment
 *
 * @package StaticSnap
 */

namespace StaticSnap\Rest;

use WP_REST_Controller;
use StaticSnap\Application;
use StaticSnap\Environments\Github_Environment;
use WP_REST_Server;

/**
 * REST Github Environment class
 * This is class is just for access the repositories without / with cache
 */
final class Rest_Github_Environment extends WP_REST_Controller {

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
	protected $rest_base = '/github-environment';
	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/installations',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'installations' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/repositories',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'repositories' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
	}

	/**
	 * Get repositories
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function installations( $request ) {

		$cache = (bool) $request->get_param( 'cache' ) ?? true;

		$installations = Github_Environment::get_github_app_user_installations( $cache );

		return rest_ensure_response( $installations );
	}

	/**
	 * Get repositories
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function repositories( $request ) {

		$page         = $request->get_param( 'page' ) ?? 1;
		$cache        = (bool) $request->get_param( 'cache' ) ?? true;
		$installation = $request->get_param( 'installation' ) ?? null;

		$repositories = Github_Environment::get_github_repositories( $installation, $page, $cache );

		return rest_ensure_response( $repositories );
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
