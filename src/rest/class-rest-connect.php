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
use StaticSnap\Config\Options;
use StaticSnap\Connect\Connect;

/**
 * REST Connect class
 * This class expose all Environment_Database methods to the REST API
 */
final class Rest_Connect extends WP_REST_Controller {

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
	protected $rest_base = '/connect';
	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'connect' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'disconnect' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);

		// check if website has a valid license.
		register_rest_route(
			$this->namespace,
			'/connect/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'status' ),
				'permission_callback' => array( $this, 'permissions' ),
			)
		);
	}

	/**
	 * Get status
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function status( $request ) {
		$default_return = array(
			'connected'                 => false,
			'has_valid_website_license' => false,
			'error_message'             => __( 'No connection data found', 'static-snap' ),
			'connection_error'          => false,
		);

		$connect_data = Connect::instance()->get_connect_data();
		if ( empty( $connect_data['installation_access_token'] ) ) {
			return rest_ensure_response(
				$default_return
			);
		}

		if ( Application::instance()->get_wp_installation_md5() !== $connect_data['website_id'] ) {
			$default_return['error_message'] = __( 'Website ID does not match', 'static-snap' );
			return rest_ensure_response(
				$default_return
			);
		}

		$static_snap_api_url = Application::instance()->get_static_snap_api_url( '/websites/check-license' );

		$response = wp_remote_post(
			$static_snap_api_url,
			array(
				// phpcs:ignore
				'body'    => json_encode(
					array(
						'website_id' => $connect_data['website_id'],
					)
				),
				'headers' => array(
					'Authorization' => 'BEARER ' . $connect_data['installation_access_token'],
					'Content-Type' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$default_return['error_message']    = $response->get_error_message();
			$default_return['connection_error'] = true;
			return rest_ensure_response(
				$default_return
			);
		}

		$response_body = wp_remote_retrieve_body( $response );

		$response_data = json_decode( $response_body, true );

		if ( 'error' === $response_data['type'] ) {

			return rest_ensure_response(
				array(
					'connected'                 => true,
					'has_valid_website_license' => false,
					'error_message'             => $response_data['error']['message'],
				)
			);
		}

		return rest_ensure_response(
			array(
				'connected'                 => true,
				'has_valid_website_license' => (bool) $response_data['data'],
				'error_message'             => null,
			)
		);
	}

	/**
	 * Cancel deployment
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function connect( $request ) {
		// get code from request.

		$code = $request->get_param( 'code' );
		if ( empty( $code ) ) {
			return rest_ensure_response(
				array(
					'connected' => false,
					'error'     => true,
					'message'   => 'Code is required',
				)
			);
		}
		$connected = Connect::instance()->connect( $code );

		return rest_ensure_response(
			array(
				'connected' => $connected,
			)
		);
	}

	/**
	 * Disconnect
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function disconnect( $request ) {
		$static_snap_disconnect = $request->get_param( 'static_snap_disconnect' ) ?? true;
		Connect::instance()->disconnect( $static_snap_disconnect );

		return rest_ensure_response(
			array(
				'disconnected' => true,
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
