<?php
/**
 * Contact Form 7 Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Forms\Contact_Form_7;

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
}
