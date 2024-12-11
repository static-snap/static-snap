<?php
/**
 * Contact Form 7 Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Forms\Contact_Form_7;

use StaticSnap\API\API;
use StaticSnap\Application;
use StaticSnap\Forms\Form_Extension_Base;


/**
 * Contact Form 7 Extension
 */
final class Contact_Form_7_Extension extends Form_Extension_Base {




	/**
	 * Constructor
	 *
	 * @param array $params Parameters.
	 */
	public function __construct( $params = array() ) {
		parent::__construct( $params );
		if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			return;
		}

		add_filter( 'wpcf7_load_js', '__return_false' );
		add_filter( 'wpcf7_form_action_url', array( $this, 'get_action_url' ), 10 );
		add_filter( 'wpcf7_form_additional_atts', array( $this, 'add_additional_attributes' ), 10, 1 );
		add_filter( 'wpcf7_form_hidden_fields', array( $this, 'add_hidden_fields' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
	}

	/**
	 * Add additional attributes
	 *
	 * @param array $atts Attributes.
	 */
	public function add_additional_attributes( $atts ) {
		$atts['data-static-snap-type']      = 'form';
		$atts['data-static-snap-form-type'] = 'contact-form-7';
		return $atts;
	}

	/**
	 * Add hidden fields
	 *
	 * @param array $fields Fields.
	 */
	public function add_hidden_fields( $fields ) {

		if ( ! class_exists( '\WPCF7_ContactForm' ) ) {
			return $fields;
		}

		$form = \WPCF7_ContactForm::get_current(); // phpcs:ignore

		$fields[ self::TOKEN_FIELD_NAME ]     = $this->get_website_token();
		$fields[ self::FORM_NAME_FIELD_NAME ] = $form->title();
		$fields[ self::FORM_ID_FIELD_NAME ]   = $form->id();

		$fields[ self::FORM_TYPE_FIELD_NAME ] = 'contact-form-7';
		return $fields;
	}

	/**
	 * Get name
	 */
	public function get_name(): string {
		return 'contact-form-7';
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		$asset_file         = include STATIC_SNAP_PLUGIN_DIR . '/assets/js/contact-form-7.asset.php';
		$asset_dependencies = $asset_file['dependencies'];
		wp_enqueue_script( 'static-snap-contact-form-7', STATIC_SNAP_PLUGIN_URL . '/assets/js/contact-form-7.js', $asset_dependencies, $asset_file['version'], true );
	}

	/**
	 * Build placeholders
	 *
	 * @param object $form The form.
	 * @return array
	 */
	public function build_placeholders( $form ) {

		$placeholders = array(
			'[_site_url]'         => get_site_url(),
			'[_site_title]'       => get_bloginfo( 'name' ),
			'[_site_description]' => get_bloginfo( 'description' ),
			'[_site_admin_email]' => get_option( 'admin_email' ),
			'[_form_id]'          => $form->id(),
			'[_form_title]'       => $form->title(),
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
	 * This method is used to sync form settings with the static snap server
	 *
	 * @return bool True if the forms settings were synced successfully; false otherwise.
	 */
	public function sync_forms_settings() {
		// check if |WPCF7_ContactForm class exists.
		if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) || ! class_exists( '\WPCF7_ContactForm' ) ) {
			return 'nooo';
		}

		$website_form_settings = array();

		$forms = \WPCF7_ContactForm::find();
		foreach ( $forms as $form ) {

			$placeholders     = $this->build_placeholders( $form );
			$email_settings   = array();
			$message_settings = array();
			if ( $form->messages ) {
				$message_settings = array(
					'success'  => $form->messages['mail_sent_ok'],
					'error'    => $form->messages['mail_sent_ng'],
					'required' => $form->messages['invalid_required'],
					'invalid'  => $form->messages['validation_error'],
				);
			}

			if ( $form->mail ) {
				$email_settings = array(
					'to'      => $this->replace_placeholders( $form->mail['recipient'], $placeholders ),
					'subject' => $this->replace_placeholders( $form->mail['subject'], $placeholders ),
					'content' => $this->replace_placeholders( $form->mail['body'], $placeholders ),
				);
			}

			if ( $form->mail_2 && $form->mail_2['active'] ) {
				$email_settings = array(
					'to'      => $this->replace_placeholders( $form->mail_2['recipient'], $placeholders ),
					'subject' => $this->replace_placeholders( $form->mail_2['subject'], $placeholders ),
					'content' => $this->replace_placeholders( $form->mail_2['body'], $placeholders ),
				);
			}

			$website_form_settings[] = array(
				'website_form_website_id'     => Application::instance()->get_wp_installation_md5(),
				'website_form_name'           => $form->title(),
				'website_form_id'             => strval( $form->id() ),
				'webiste_form_extension_name' => $this->get_name(),
				'website_form_settings'       => array(
					'email'             => $email_settings,
					'redirect_settings' => null,
					'popup_settings'    => array(),
					'webhooks'          => array(),
					'messages'          => $message_settings,
				),
			);

		}

		$api = new API();
		$api->post(
			'/website-forms/sync/' . $this->get_name(),
			array(
				'website_id' => Application::instance()->get_wp_installation_md5(),
				'data'       => $website_form_settings,
			)
		);

		return true;
	}
}
