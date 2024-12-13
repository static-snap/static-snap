<?php
/**
 * Contact Form 7 Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Forms\WP_Forms;

use StaticSnap\API\API;
use StaticSnap\Forms\Form_Extension_Base;
use StaticSnap\Application;
use StaticSnap\Connect\Connect;

/**
 * Contact Form 7 Extension
 */
final class WP_Forms_Extension extends Form_Extension_Base {

	/**
	 * Constructor
	 *
	 * @param array $params Parameters.
	 */
	public function __construct( $params = array() ) {

		parent::__construct( $params );
		if ( ! is_plugin_active( 'wpforms-lite/wpforms.php' ) ) {
			return;
		}

		add_filter( 'wpforms_frontend_form_action', array( $this, 'get_action_url' ), 10 );
		add_filter( 'wpforms_frontend_form_atts', array( $this, 'add_additional_attributes' ), 10, 2 );
		add_action( 'wpforms_display_submit_before', array( $this, 'add_hidden_fields' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
	}

	/**
	 * Add additional attributes
	 *
	 * @param array $atts Attributes.
	 * @param array $form_data Form data.
	 */
	public function add_additional_attributes( $atts, $form_data ) {

		$atts['atts']['data-static-snap-type']      = 'form';
		$atts['atts']['data-static-snap-form-type'] = $this->get_name();
		$expected_confirmation_settings             = array(
			'type'     => 'message',
			'message'  => '',
			'redirect' => '',
			'page'     => '',
		);

		// just in case the confirmations are not set.
		$confirmation_settings = array_merge( $expected_confirmation_settings, array_shift( $form_data['settings']['confirmations'] ) );

		if ( ! is_array( $confirmation_settings ) ) {
			return $atts;
		}
		if ( 'page' === $confirmation_settings['type'] ) {
			$confirmation_settings['redirect'] = get_permalink( $confirmation_settings['page'] );
			$confirmation_settings['type']     = 'page';
		}

		$atts['atts']['data-static-snap-form-notice-settings'] = wp_json_encode(
			array(
				'success_message' => $confirmation_settings['message'],
				'redirect_url'    => $confirmation_settings['redirect'],
				'type'            => $confirmation_settings['type'],
			)
		);

		return $atts;
	}



	/**
	 * Add hidden field
	 *
	 * @param string $name  Field name.
	 * @param string $value Field value.
	 */
	private function add_hidden_field( $name, $value ) {
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
	}

	/**
	 * Add hidden fields
	 *
	 * @param array $form_data Form data.
	 */
	public function add_hidden_fields( $form_data ) {

		$this->add_hidden_field( self::TOKEN_FIELD_NAME, $this->get_website_token() );
		$this->add_hidden_field( self::FORM_NAME_FIELD_NAME, $form_data['settings']['form_title'] );
		$this->add_hidden_field( self::FORM_ID_FIELD_NAME, $form_data['id'] );
		$this->add_hidden_field( self::FORM_TYPE_FIELD_NAME, $this->get_name() );
	}

	/**
	 * Get name
	 */
	public function get_name(): string {
		return 'wp-forms';
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		$asset_file         = include STATIC_SNAP_PLUGIN_DIR . '/assets/js/wp-forms.asset.php';
		$asset_dependencies = $asset_file['dependencies'];
		wp_enqueue_script( 'static-snap-wp-forms', STATIC_SNAP_PLUGIN_URL . '/assets/js/wp-forms.js', $asset_dependencies, $asset_file['version'], true );
	}


	/**
	 * Build placeholders
	 *
	 * @param object $form The form.
	 * @return array
	 */
	/**
	 * Build placeholders
	 *
	 * @param object $form The form.
	 * @return array
	 */
	public function build_placeholders( $form ) {

		$placeholders = array(
			// Basic Smart Tags.
			'{admin_email}'         => get_option( 'admin_email' ),
			'{form_id}'             => $form->ID,
			'{form_name}'           => $form->post_name,
			'{page_title}'          => get_the_title(),
			'{page_url}'            => get_site_url(),
			'{date format="m/d/Y"}' => gmdate( 'm/d/Y' ),
			'{unique_value}'        => uniqid(),

			// Author Details.
			'{author_id}'           => get_the_author_meta( 'ID' ),
			'{author_display}'      => get_the_author(),
			'{author_email}'        => get_the_author_meta( 'email' ),

			// User Management.
			'{user_display}'        => wp_get_current_user()->display_name ?? '',
			'{user_full_name}'      => trim( wp_get_current_user()->first_name . ' ' . wp_get_current_user()->last_name ),
			'{user_first_name}'     => wp_get_current_user()->first_name ?? '',
			'{user_last_name}'      => wp_get_current_user()->last_name ?? '',
			'{user_email}'          => wp_get_current_user()->user_email ?? '',

			'{url_login}'           => wp_login_url(),
			'{url_logout}'          => wp_logout_url(),
			'{url_register}'        => wp_registration_url(),
			'{url_lost_password}'   => wp_lostpassword_url(),

			// User Registration.
			'{site_name}'           => get_bloginfo( 'name' ),

		);

		return $placeholders;
	}

	/**
	 * Replace placeholders
	 * This method is used to replace placeholders in the form settings
	 *
	 * @param string $str The string to replace the placeholders in.
	 * @param array  $placeholders The placeholders to replace.
	 */
	public function replace_placeholders( $str, $placeholders ) {
		foreach ( $placeholders as $placeholder => $value ) {
			$str = str_replace( $placeholder, $value, $str );
		}
		return $str;
	}

	/**
	 * Sync forms settings
	 */
	public function sync_forms_settings() {

		if ( ! is_plugin_active( 'wpforms-lite/wpforms.php' ) ) {
			return;
		}
		$website_form_settings = array();

		$forms = \WPForms()->form->get();
		foreach ( $forms as $index => $form ) {
			$email_settings    = array();
			$redirect_settings = null;
			$popup_settings    = array();
			$webhooks_settings = array();
			$success_message   = null;
			try {
				$decoded_form_json = json_decode( $form->post_content, true );
				$placeholders      = $this->build_placeholders( $form );

				if ( ! is_array( $decoded_form_json ) ) {
					continue;
				}
				$form_settings = $decoded_form_json['settings'] ?? array();

				if ( ! is_array( $form_settings ) ) {
					continue;
				}

				// check confirmations.
				$confirmations = $form_settings['confirmations'] ?? array();

				foreach ( $confirmations as $confirmation ) {
					if ( 'message' === $confirmation['type'] ) {
						$success_message = $this->replace_placeholders( $confirmation['message'], $placeholders );
					}
					if ( 'redirect' === $confirmation['type'] ) {
						$redirect_settings = $confirmation['redirect'];
					}
					if ( 'page' === $confirmation['type'] ) {
						$redirect_settings = get_permalink( $confirmation['page'] );
					}
				}

				$notifications = $form_settings['notifications'] ?? array();

				foreach ( $notifications as $notification ) {
					$email_settings = array(
						'to'        => $this->replace_placeholders( $notification['email'], $placeholders ),
						'subject'   => $this->replace_placeholders( $notification['subject'], $placeholders ),
						'from_name' => $this->replace_placeholders( $notification['sender_name'], $placeholders ),
						'content'   => $this->replace_placeholders( $notification['message'], $placeholders ),
					);
				}

				$website_form_settings[] = array(
					'website_form_website_id'     => Application::instance()->get_wp_installation_md5(),
					'website_form_name'           => $form->post_name,
					'website_form_id'             => strval( $form->ID ),
					'webiste_form_extension_name' => $this->get_name(),
					'website_form_settings'       => array(
						'submit_actions' => empty( $email_settings ) ? array() : array( 'email' ),
						'email'          => $email_settings,
						'redirect_to'    => $redirect_settings,
						'popup'          => $popup_settings,
						'webhooks'       => $webhooks_settings,
						'messages'       => array(
							'success' => $success_message,
						),
					),
				);

			} catch ( \Exception $e ) {
				continue;
			}

			$api = new API();
			$api->post(
				'/website-forms/sync/' . $this->get_name(),
				array(
					'website_id' => Application::instance()->get_wp_installation_md5(),
					'data'       => $website_form_settings,
				)
			);
		}

		return true;
	}
}
