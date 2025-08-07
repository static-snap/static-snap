<?php
/**
 * Static Snap Connect
 *
 * @package StaticSnap
 */

namespace StaticSnap\Connect;

use StaticSnap\Application;
use StaticSnap\Config\Options;
use StaticSnap\Traits\Singleton;
use StaticSnap\Base;

/**
 * Class to manage connect.
 */
final class Connect extends Base {


	use Singleton;

	/**
	 * Error
	 *
	 * @var string
	 */
	private $error = '';


	/**
	 * Constructor for Connect class.
	 */
	//phpcs:ignore
	private function __construct() {
		parent::__construct();
	}

	/**
	 * Get last error.
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Connect to the app.
	 *
	 * @param string $code Code.
	 */
	public function connect( $code ) {
		// get code from request.

		$connected = false;
		$data      = array();

		// in case we have google recaptcha secret key.
		$website_captcha_secret_key = Options::instance()->get( 'forms._captcha_secret_key', '' );
		$website_captch_type        = Options::instance()->get( 'forms.captcha_type', '' );

		$response = wp_remote_post(
			$this->app->get_static_snap_api_url( '/websites/connect' ),
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode(
					array(
						'code'                       => $code,
						'website_id'                 => Application::instance()->get_wp_installation_md5(),
						'website_url'                => get_site_url(),
						'website_name'               => get_bloginfo( 'name' ),
						'website_captcha_type'       => $website_captch_type,
						'website_captcha_secret_key' => $website_captcha_secret_key,
					)
				),
			)
		);

		$is_wp_error = is_wp_error( $response );

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( ! $is_wp_error && ! empty( $data['data']['installation_id'] ) ) {
			$connected = true;
			// save data.
			$options = Options::instance();
			$options->set( 'connect', $data['data'] );
			$options->save();
		}

		// if error set the response error.
		if ( $is_wp_error ) {
			$this->error = $response->get_error_message();
		}

		return $connected;
	}

	/**
	 * Disconnect
	 *
	 * @param bool $static_snap_disconnect Call static snap remote disconnect.
	 */
	public function disconnect( $static_snap_disconnect = true ) {
		$connect_data = $this->get_connect_data();
		if ( empty( $connect_data ) ) {
			return;
		}
		$required_fields = array( 'website_id', 'installation_access_token' );
		foreach ( $required_fields as $field ) {
			if ( empty( $connect_data[ $field ] ) ) {
				return;
			}
		}
		if ( $static_snap_disconnect ) {

			wp_remote_post(
				$this->app->get_static_snap_api_url( '/websites/disconnect' ),
				array(
					'body' => wp_json_encode(
						array(
							'website_id' => $connect_data['website_id'],

						)
					),
					'headers' => array(
						'Authorization' => 'Bearer ' . $connect_data['installation_access_token'],
						'Content-Type'  => 'application/json',
					),
				)
			);
		}

		$options = Options::instance();
		$options->delete( 'connect' );
		$options->save();
	}

	/**
	 * Refresh github token
	 *
	 * @return array
	 */
	public function refresh_github_token() {

		$options                      = Options::instance();
		$access_token                 = $options->get( 'connect' )['installation_access_token'];
		$installation_id              = $options->get( 'connect' )['installation_id'];
		$installation_subscription_id = $options->get( 'connect' )['installation_subscription_id'];

		$response = wp_remote_post(
			$this->app->get_static_snap_api_url( '/github/refresh-access-token' ),
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode(
					array(
						'access_token'    => $access_token,
						'installation_id' => $installation_id,
						'subscription_id' => $installation_subscription_id,
					)
				),
			)
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_wp_error( $response ) && 'success' === $data['status'] && ! empty( $data['data']['installation_access_token'] ) ) {
			// Keep website_* fields.
			// TODO we need to fix this.
			$merge = array_merge( $options->get( 'connect' ), $data );
			$options->set( 'connect', $merge );
			$options->save();
		}

		return $data;
	}

	/**
	 * Check if github token is expired
	 *
	 * @return array
	 */
	public function is_github_token_expired() {
		$connect = $this->get_connect_data();
		if ( empty( $connect['installation_expires_at'] ) ) {
			return $connect;
		}

		$expired_datetime = new \DateTime( $connect['installation_expires_at'] );
		$now              = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );

		if ( $now > $expired_datetime ) {

			$connect = $this->refresh_github_token();
		}

		return $connect;
	}

	/**
	 * Get connect data
	 *
	 * @return array
	 */
	public function get_connect_data() {
		$options = Options::instance();
		$connect = $options->get( 'connect' );
		return $connect;
	}
}
