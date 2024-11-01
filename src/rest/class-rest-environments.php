<?php
/**
 * REST Environments
 *
 * @package StaticSnap
 */

namespace StaticSnap\Rest;

use WP_REST_Controller;
use StaticSnap\Application;
use StaticSnap\Constants\Build_Type;
use StaticSnap\Database\Environments_Database;
use StaticSnap\Environments\Environment;
use StaticSnap\Environments\Environment_Type_Factory;
use WP_REST_Server;

/**
 * REST Environments class
 * This class expose all Environment_Database methods to the REST API
 */
final class Rest_Environments extends WP_REST_Controller {

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
	protected $rest_base = '/environments';
	/**
	 * Register the routes
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/types',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_types' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'permissions' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'permissions' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
						),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/publish',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'publish' ),
					'permission_callback' => array( $this, 'permissions' ),
				),
			)
		);
	}

	/**
	 * Get types
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_types( $request ) {
		$types = Application::instance()->get_extensions_by_type( 'environment_type' );

		return rest_ensure_response( $types );
	}
	/**
	 * Get environments
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$environments = Environments_Database::instance()->get_all();

		return rest_ensure_response( $environments );
	}

	/**
	 * Get environment by id
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_item( $request ) {
		$environment = Environments_Database::instance()->get_by_id( $request['id'] );

		return rest_ensure_response( $environment->to_array( false ) );
	}

	/**
	 * Create environment
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_item( $request ) {
		$environmnent = Environment::from_array( $request->get_params(), false );
		$result       = Environments_Database::instance()->insert( $environmnent );

		return rest_ensure_response( $result );
	}

	/**
	 * Update environment
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function update_item( $request ) {
		if ( Application::instance()->get_deployment()->is_active() ) {
			return rest_ensure_response( new \WP_Error( 'deploying', 'Deployment is in progress', array( 'status' => 400 ) ) );
		}
		$instance = Environment_Type_Factory::create( $request->get_params() );
		if ( ! $instance->is_configured() ) {
			return rest_ensure_response( array( 'errors' => $instance->get_errors() ) );
		}

		$environmnent = Environment::from_array( $request->get_params(), false );
		$response     = Environments_Database::instance()->update( $request['id'], $environmnent );
		return rest_ensure_response( $response );
	}

	/**
	 * Delete environment
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function delete_item( $request ) {
		if ( Application::instance()->get_deployment()->is_active() ) {
			return rest_ensure_response( new \WP_Error( 'deploying', 'Deployment is in progress', array( 'status' => 400 ) ) );
		}

		$environment = Environments_Database::instance()->delete_by_id( $request['id'] );

		return rest_ensure_response( $environment );
	}

	/**
	 * Publish environment
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function publish( $request ) {
		$environment = Environments_Database::instance()->get_by_id( $request['id'] );
		$build_type  = $request['build_type'] ?? Build_Type::FULL;

		if ( ! Build_Type::is_valid_build_type( $build_type ) ) {
			return rest_ensure_response( new \WP_Error( 'invalid_build_type', 'Invalid build type', array( 'status' => 400 ) ) );
		}

		/**
		 * Steps are
		 * Publish
		 * ...
		 * ..
		 * Deploy
		 *
		 * @see StaticSnap\Environments\Environment::deploy
		 */
		return rest_ensure_response( $environment->publish( $build_type ) );
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
