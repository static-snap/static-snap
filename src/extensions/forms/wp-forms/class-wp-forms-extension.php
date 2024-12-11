<?php
/**
 * Contact Form 7 Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Forms\WP_Forms;

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
		$atts['atts']['data-static-snap-form-type'] = 'wpform';
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
		$this->add_hidden_field( self::FORM_TYPE_FIELD_NAME, 'wpforms' );
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
}
