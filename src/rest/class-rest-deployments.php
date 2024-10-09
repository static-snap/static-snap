<?php
/**
 * REST Deployments
 *
 * @package StaticSnap
 */

namespace StaticSnap\Rest;

use WP_REST_Controller;
use StaticSnap\Application;
use WP_REST_Server;

/**
 * REST Deployments class
 * This class expose all Environment_Database methods to the REST API
 */
final class Rest_Deployments extends WP_REST_Controller {

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
	protected $rest_base = '/deployments';
	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/cancel',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'cancel' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
	}

	/**
	 * Cancel deployment
	 *
	 * @return \WP_REST_Response
	 */
	public function cancel() {
		Application::instance()->cancel_deployment();

		return rest_ensure_response( array( 'status' => 'canceled' ) );
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
