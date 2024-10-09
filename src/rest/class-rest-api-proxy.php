<?php
/**
 *  Rest Api Proxy
 *
 * @package StaticSnap
 */

namespace StaticSnap\Rest;

use StaticSnap\API\API;
use WP_REST_Controller;
use WP_REST_Server;

/**
 * Rest Api Proxy
 * This class will proxy the static snap api requests
 */
final class Rest_Api_Proxy extends WP_REST_Controller {
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
	protected $rest_base = '/api-proxy';

	/**
	 * API
	 *
	 * @var \StaticSnap\API\API
	 */
	private $api = null;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->api = new API();
	}

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
					'callback'            => array( $this, 'get' ),
					'permission_callback' => array( $this, 'permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'post' ),
					'permission_callback' => array( $this, 'permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete' ),
					'permission_callback' => array( $this, 'permissions' ),
				),

			)
		);
	}



	/**
	 * Get
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get( $request ) {

		$action = $request->get_query_params()['action'] ?? '';

		if ( empty( $action ) ) {
			return new \WP_Error( 'invalid_action', 'Action is required', array( 'status' => 400 ) );
		}

		return $this->api->get( $action );
	}
	/**
	 * Post
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function post( $request ) {
		$action = $request->get_query_params()['action'] ?? '';
		if ( empty( $action ) ) {
			return new \WP_Error( 'invalid_action', 'Action is required', array( 'status' => 400 ) );
		}

		$body = $request->get_body();

		return $this->api->post( $action, $body );
	}

	/**
	 * Delete
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete( $request ) {
		$action = $request->get_query_params()['action'] ?? '';

		if ( empty( $action ) ) {
			return new \WP_Error( 'invalid_action', 'Action is required', array( 'status' => 400 ) );
		}

		return $this->api->delete( $action );
	}



	/**
	 * Permissions
	 * Check if the current user has the required permissions
	 * to perform the action.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return boolean
	 */
	public function permissions( $request ) {
		return current_user_can( 'manage_options' );
	}
}
