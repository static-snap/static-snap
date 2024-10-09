<?php
/**
 * Static Snap API
 *
 * @package StaticSnap
 */

namespace StaticSnap\API;

use StaticSnap\Application;
use StaticSnap\Connect\Connect;

/**
 * Class API
 * This calss is used to handle all API requests
 */
final class API {
	/**
	 * Static snap api request
	 *
	 * @param string $path Path.
	 * @return mixed
	 */
	private function get_request_params( $path ) {
		$endpoint     = Application::instance()->get_static_snap_api_url( $path );
		$connect      = Connect::instance()->get_connect_data();
		$access_token = $connect['installation_access_token'];

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			),
		);

		return array(
			'endpoint' => $endpoint,
			'args'     => $args,
		);
	}


	/**
	 * Get
	 *
	 * @param string $action Action.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get( $action ) {

		$params = $this->get_request_params( $action );

		$response = wp_remote_get(
			$params['endpoint'],
			$params['args']
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return $data;
	}
	/**
	 * Post
	 *
	 * @param string $action Action.
	 * @param array  $body Body.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function post( $action, $body ) {

		$params = $this->get_request_params( $action );

		$response = wp_remote_post(
			$params['endpoint'],
			array_merge(
				$params['args'],
				array(
					'body' => wp_json_encode( $body ),
				)
			)
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return $data;
	}

	/**
	 * Delete
	 *
	 * @param string $action Action.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete( $action ) {

		$params = $this->get_request_params( $action );

		$response = wp_remote_request(
			$params['endpoint'],
			array_merge(
				$params['args'],
				array(
					'method' => 'DELETE',
				)
			)
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return $data;
	}
}
