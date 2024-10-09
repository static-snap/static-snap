<?php
/**
 * REST
 * This class is used to handle all REST API requests
 *
 * @package StaticSnap
 */

namespace StaticSnap\Rest;

use StaticSnap\Application;
use StaticSnap\Environments\Environment_Type_Factory;

/**
 * This class is used to handle all REST API requests.
 */
final class Rest {
	/**
	 * Rest classes
	 *
	 * @var array
	 */
	private $rest_classes = array();

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		$this->rest_classes [] = new Rest_Api_Proxy();
		$this->rest_classes [] = new Rest_Options();
		$this->rest_classes [] = new Rest_Extensions();
		$this->rest_classes [] = new Rest_Environments();
		$this->rest_classes [] = new Rest_Deployments();
		$this->rest_classes [] = new Rest_Deployments_History();
		$this->rest_classes [] = new Rest_Connect();
		$this->rest_classes [] = new Rest_Github_Environment();
		$this->rest_classes [] = new Rest_Build_Options();
	}

	/**
	 * Register the routes
	 */
	public function register_routes() {
		foreach ( $this->rest_classes as $rest_class ) {
			$rest_class->register_routes();
		}

		register_rest_route(
			'static-snap/v1',
			'/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
	}


	/**
	 * Add environment
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function add_environment( \WP_REST_Request $request ) {
		$params   = $request->get_params();
		$instance = Environment_Type_Factory::create( $params );
		if ( ! $instance->is_configured() ) {
			return array( 'errors' => $instance->get_errors() );
		}

		$environments   = Application::instance()->get_environments();
		$environments[] = $params;

		Application::instance()->update_environments( $environments );
		return true;
	}
	/**
	 * Update environment
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return bool | array
	 */
	public function update_environment( \WP_REST_Request $request ) {
		$index  = $request->get_param( 'index' );
		$params = $request->get_params();
		unset( $params['index'] );

		$instance = Environment_Type_Factory::create( $params );
		if ( ! $instance->is_configured() ) {
			return array( 'errors' => $instance->get_errors() );
		}

		$environments           = Application::instance()->get_environments();
		$environments[ $index ] = $params;

		Application::instance()->update_environments( $environments );
		return true;
	}

	/**
	 * Get deployments
	 *
	 * @return array
	 */
	public function get_deployments() {
		return Application::instance()->get_deployments();
	}

	/**
	 * Get Status
	 *
	 * @return array
	 */
	public function get_status() {
		return Application::instance()->get_status();
	}

	/**
	 * Run deployment
	 *
	 * @return bool
	 */
	public function deployment_run() {
		Application::instance()->run_deployment();
		return true;
	}

	/**
	 * Pause deployment
	 *
	 * @return bool
	 */
	public function deployment_pause() {
		Application::instance()->pause_deployment();
		return true;
	}

	/**
	 * Cancel deployment
	 *
	 * @return bool
	 */
	public function deployment_cancel() {
		Application::instance()->cancel_deployment();
		return true;
	}

		/**
		 * Run test
		 *
		 * @return bool
		 */

	/**
	 * Rest permissions
	 *
	 * @return bool
	 */
	public function permissions() {
		return current_user_can( 'manage_options' );
	}
}
