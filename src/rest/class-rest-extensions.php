<?php
/**
 * REST Environments
 *
 * @package StaticSnap
 */

namespace StaticSnap\Rest;

use WP_REST_Controller;
use StaticSnap\Application;
use StaticSnap\Database\Environments_Database;
use StaticSnap\Environments\Environment;
use StaticSnap\Environments\Environment_Type_Factory;
use WP_REST_Server;

/**
 * REST Environments class
 * This class expose all Environment_Database methods to the REST API
 */
final class Rest_Extensions extends WP_REST_Controller {

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
	protected $rest_base = '/extensions';
	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'permissions' ),
				),

			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<type>[a-zA-Z0-9-_]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'permissions' ),
			),
		);
	}


	/**
	 * Get environments
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {

		$type = $request->get_param( 'type' );

		if ( $type ) {
			$extensions = Application::instance()->get_extensions_by_type( $type );

			return rest_ensure_response( $extensions );
		}

		$extensions = Application::instance()->get_extensions();

		return rest_ensure_response( $extensions );
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
