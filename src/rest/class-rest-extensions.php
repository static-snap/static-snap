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
			$this->rest_base . '/forms/sync',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'sync_forms' ),
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
	 * Sync forms
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function sync_forms( $request ) {
		$extensions = Application::instance()->get_extensions_by_type( 'form' );
		$sync_data  = array();
		try {
			foreach ( $extensions as $extension_name => $extension ) {
				// just in case it fails with an exception, by default we set it to false.
				$sync_data[ $extension_name ] = false;
				$sync_data[ $extension_name ] = $extension->sync_forms_settings();
			}
			$extension->sync_forms_settings();
		} catch ( \Exception $e ) {
			return rest_ensure_response(
				array(
					'saved'      => false,
					'extensions' => $sync_data,
					'message'    => $e->getMessage(),
				)
			);
		}

		return rest_ensure_response(
			array(
				'saved'      => true,
				'extensions' => $sync_data,
				'message'    => __( 'Forms synced successfully', 'static-snap' ),
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
