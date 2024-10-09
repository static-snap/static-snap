<?php
/**
 *  Rest options
 *
 * @package StaticSnap
 */

namespace StaticSnap\Rest;

use StaticSnap\Application;
use WP_REST_Controller;
use WP_REST_Server;

/**
 * REST Options class
 * This class expose all Options methods to the REST API
 * and creates new options using StaticSnap\Config\Options to store the data
 */
final class Rest_Options extends WP_REST_Controller {
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
	protected $rest_base = '/options';
	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'permissions' ),
				),

			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<name>[a-zA-Z0-9-_]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'permissions' ),
			),
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<name>[a-zA-Z0-9-_]+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'permissions' ),
			),
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<name>[a-zA-Z0-9-_]+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'permissions' ),
			),
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<name>[a-zA-Z0-9-_]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'permissions' ),
			),
		);
	}

	/**
	 * Get items
	 * Get all options
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$options = Application::instance()->options()->get_options();
		return rest_ensure_response( $options );
	}

	/**
	 * Get item
	 * Get a single option
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_item( $request ) {
		$name  = $request->get_param( 'name' );
		$value = Application::instance()->options()->get( $name );

		return rest_ensure_response( $value );
	}

	/**
	 * Create item
	 * Create a new option
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_item( $request ) {
		$name = $request->get_param( 'name' );
		// values are on json body.
		$value = $request->get_json_params();
		try {
			Application::instance()->options()->set( $name, $value );
			Application::instance()->options()->save();
			return rest_ensure_response( $value );
		} catch ( \Exception $e ) {
			// response an error message.
			$wp_error = new \WP_Error(
				'error',
				$e->getMessage(),
				array(
					'name'  => $name,
					'value' => $value,
				)
			);
			return rest_ensure_response( $wp_error );
		}
	}

	/**
	 * Update item
	 * Update an option
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function update_item( $request ) {
		$name  = $request->get_param( 'name' );
		$value = $request->get_param( 'value' );
		try {
			Application::instance()->options()->set( $name, $value );
			Application::instance()->options()->save();
			return rest_ensure_response( $value );
		} catch ( \Exception $e ) {
			$wp_error = new \WP_Error(
				'error',
				$e->getMessage(),
				array(
					'name'  => $name,
					'value' => $value,
				)
			);
			return rest_ensure_response( $wp_error );
		}
	}

	/**
	 * Delete item
	 * Delete an option
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function delete_item( $request ) {
		$name = $request->get_param( 'name' );
		Application::instance()->options()->delete( $name );
		$saved = Application::instance()->options()->save();
		return rest_ensure_response( $saved );
	}
	/**
	 * Permissions
	 * Check if the user has the right permissions
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function permissions( $request ) {
		return current_user_can( 'manage_options' );
	}
}
