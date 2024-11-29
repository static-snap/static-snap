<?php
/**
 * Forms base class
 *
 * @package StaticSnap
 */

namespace StaticSnap\Forms;

use StaticSnap\Constants\Filters;
use StaticSnap\Extension\Extension_Base;
use StaticSnap\Connect\Connect;
use StaticSnap\Application;
use StaticSnap\Config\Options;

/**
 * Base extension class for Forms
 */
abstract class Form_Extension_Base extends Extension_Base {


	protected const TOKEN_FIELD_NAME = 'static_snap_website_token';

	protected const FORM_NAME_FIELD_NAME = 'static_snap_form_name';


	protected const FORM_ID_FIELD_NAME = 'static_snap_form_id';

	protected const FORM_TYPE_FIELD_NAME = 'static_snap_form_type';


	/**
	 * Constructor
	 *
	 * @param array $params The parameters.
	 */
	public function __construct( $params = array() ) {
		parent::__construct( $params );
		// check if this filter is already added.
		if ( ! has_filter( Filters::SET_OPTIONS . '_forms', array( $this, 'on_set_options' ) ) ) {
			add_filter( Filters::SET_OPTIONS . '_forms', array( $this, 'on_set_options' ), 10, 1 );
		}
		if ( ! has_filter( Filters::FRONTEND_LOCALIZE_DATA, array( $this, 'frontend_localize_data' ) ) ) {
			add_filter( Filters::FRONTEND_LOCALIZE_DATA, array( $this, 'frontend_localize_data' ), 10, 1 );
		}
	}

	/**
	 * Get type
	 */
	public function get_type(): string {
		return 'form';
	}

	/**
	 * Get website token
	 *
	 * @return string
	 */
	public function get_website_token(): string {
		$connect      = Connect::instance();
		$connect_data = $connect->get_connect_data();
		return $connect_data['website_token'] ?? '';
	}

	/**
	 * Get action URL
	 *
	 * @return string
	 */
	public function get_action_url(): string {
		return Application::instance()->get_static_snap_api_url( '/forms/submit', 'frontend' );
	}

	/**
	 * Is enabled
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		// get forms enabled option.
		return (bool) Options::instance()->get( 'forms.enabled', false );
	}

	/**
	 * Is configured
	 */
	public function is_configured(): bool {
		return true;
	}

	/**
	 * Get Settings fields
	 */
	public function get_settings_fields(): array {
		return array();
	}

	/**
	 * Get recaptcha site key
	 */
	public function get_recaptcha_site_key() {
		return Options::instance()->get( 'forms.google_recaptcha_site_key' );
	}

	/**
	 * Get recaptcha secret key
	 */
	public function get_recaptcha_secret_key(): string {
		return Options::instance()->get( 'forms._google_recaptcha_secret_key' );
	}


	/**
	 * Set options
	 *
	 * @param array $options The options.
	 * @return array
	 * @throws \Exception If an error occurs.
	 */
	public function on_set_options( $options ) {
		// if disabled return options.
		if ( ! $options['enabled'] ) {
			return $options;
		}
		// get google recaptcha site key and secret key.
		$google_recaptcha_site_key   = $options['google_recaptcha_site_key'] ?? '';
		$google_recaptcha_secret_key = $options['_google_recaptcha_secret_key'] ?? '';
		// check if google recaptcha site key and secret are valid.
		if ( empty( $google_recaptcha_site_key ) || empty( $google_recaptcha_secret_key ) ) {
			throw new \Exception( 'Google Recaptcha Site Key and Secret Key are required.' );
		}

		$options_instance = Options::instance();
		$access_token     = $options_instance->get( 'connect' )['installation_access_token'];

		$response = wp_remote_post(
			$this->app->get_static_snap_api_url( '/websites/update-recaptcha-secret-key' ),
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
				'body' => wp_json_encode(
					array(
						'website_id'                  => Application::instance()->get_wp_installation_md5(),
						'google_recaptcha_secret_key' => $google_recaptcha_secret_key,
					)
				),
			)
		);

			$is_wp_error = is_wp_error( $response );

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

		if ( ! $is_wp_error && 'item' === $data['type'] && $data['data'] ) {

			return $options; // secret key is valid.
		} else {
			$error_message = $data['error']['message'];
			throw new \Exception( esc_html( $error_message ) );
		}

		// phpcs:ignore
		throw new \Exception( __( 'Google Recaptcha Site Key and Secret Key are invalid.', 'static-snap' ) );
	}

	/**
	 * Frontend localize data
	 *
	 * @param array $data The data.
	 * @return array
	 */
	public function frontend_localize_data( $data ) {
		$data['recaptcha_site_key'] = $this->get_recaptcha_site_key();
		return $data;
	}

	/**
	 * Sync forms settings
	 * This method is used to sync form settings with the static snap server
	 *
	 * @return bool
	 */
	public function sync_forms_settings() {
		return false;
	}
}
