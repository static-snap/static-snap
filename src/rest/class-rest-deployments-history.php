<?php
/**
 * REST Deployments
 *
 * @package StaticSnap
 */

namespace StaticSnap\Rest;

use WP_REST_Controller;
use StaticSnap\Application;
use StaticSnap\Database\Deployment_History_Database;
use StaticSnap\Database\Environments_Database;
use StaticSnap\Deployments\Environment;
use WP_REST_Server;

/**
 * REST Deployments class
 * This class expose all Environment_Database methods to the REST API
 */
final class Rest_Deployments_History extends WP_REST_Controller {

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
	protected $rest_base = '/deployments-history';
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
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'permissions' ),
				),

			)
		);
		// delete all.
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/delete-all',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_all' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/download/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'download' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		// last deployment for environment.
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/last/(?P<id>\d+)',
			array(
				'args' => array(
					'id' => array(
						'description' => 'Environment ID',
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_last' ),
					'permission_callback' => array( $this, 'permissions' ),
				),
			)
		);
	}

	/**
	 * Get environments
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$deployments                      = Deployment_History_Database::instance()->get_all();
		$last_environmnets_deployments_id = array();
		foreach ( $deployments as &$deployment ) {
			if ( ! isset( $last_environmnets_deployments_id[ $deployment['environment_id'] ] ) ) {
				$last_deployment = Deployment_History_Database::instance()->get_last_completed_history_by_environment( (int) $deployment['environment_id'] );
				$last_environmnets_deployments_id[ $deployment['environment_id'] ] = $last_deployment['id'];
			}

			$deployment['is_last_deployment'] = $last_environmnets_deployments_id[ $deployment['environment_id'] ] === $deployment['id'];
		}

		return rest_ensure_response( $deployments );
	}

	/**
	 * Get environment by id
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_item( $request ) {
		$environment = Deployment_History_Database::instance()->get_by_id( $request['id'] );

		return rest_ensure_response( $environment->to_array( false ) );
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

		$environment = Deployment_History_Database::instance()->delete_by_id( $request['id'] );

		return rest_ensure_response( $environment );
	}

	/**
	 * Publish environment
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function delete_all( $request ) {
		$response = Deployment_History_Database::instance()->delete_all();

		return rest_ensure_response( $response );
	}

	/**
	 * Get last deployment for environment
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_last( $request ) {

		$deployment = Deployment_History_Database::instance()->get_last_completed_history_by_environment( (int) $request['id'] );

		return rest_ensure_response( $deployment );
	}

	/**
	 * Download deployment
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function download( $request ) {
		$deployment = Deployment_History_Database::instance()->get_by_id( (int) $request['id'] );

		if ( empty( $deployment ) ) {
			return rest_ensure_response( new \WP_Error( 'not_found', 'Deployment not found', array( 'status' => 404 ) ) );
		}

		$environment     = Environments_Database::instance()->get_by_id( (int) $deployment['environment_id'] );
		$last_deployment = Deployment_History_Database::instance()->get_last_completed_history_by_environment( (int) $deployment['environment_id'] );

		$is_last_deployment = $last_deployment['id'] === $deployment['id'];

		$zip_file = $environment->get_zip_file_name();

		$zip_file_path = $environment->get_build_path() . '/' . $zip_file;

		if ( ! file_exists( $zip_file_path ) ) {
			return rest_ensure_response( new \WP_Error( 'not_found', 'Deployment zip file not found', array( 'status' => 404 ) ) );
		}

		$zip_file_url = $environment->get_build_path() . '/' . $zip_file;
		$zip_file_url = str_replace( ABSPATH, site_url( '/' ), $zip_file_url );
		// add deployment id to the URL to avoid caching.
		$zip_file_url = add_query_arg( 'deployment_id', $deployment['id'], $zip_file_url );

		return rest_ensure_response(
			array(
				'url'                => $zip_file_url,
				'is_last_deployment' => $is_last_deployment,
				'last_deployment'    => $last_deployment,
			)
		);
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
