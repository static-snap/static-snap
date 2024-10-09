<?php
/**
 * Contact Form 7 Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Forms\Gravity_Forms;

use StaticSnap\Forms\Form_Extension_Base;
use StaticSnap\Application;
use StaticSnap\Connect\Connect;

/**
 * Contact Form 7 Extension
 */
final class Gravity_Forms_Extension extends Form_Extension_Base {

	/**
	 * Constructor
	 *
	 * @param array $params Parameters.
	 */
	public function __construct( $params = array() ) {

		parent::__construct( $params );

		if ( ! is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
			return;
		}

		add_filter( 'gform_form_tag', array( $this, 'add_additional_attributes' ), 10, 2 );
		add_filter( 'gform_form_args', array( $this, 'disable_ajax' ), 10, 1 );
		add_filter( 'gform_field_content', array( $this, 'add_require_to_field' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
	}

	/**
	 * Disable ajax
	 *
	 * @param array $form_args Form args.
	 * @return array
	 */
	public function disable_ajax( $form_args ) {
		$form_args['ajax'] = false;
		return $form_args;
	}

	/**
	 * Add require to field
	 *
	 * @param string $field_content Field content.
	 * @param array  $field         Field.
	 */
	public function add_require_to_field( $field_content, $field ) {
		if ( 'hidden' === $field['type'] ) {
			return $field_content;
		}
		if ( ! isset( $field['isRequired'] ) || ! $field['isRequired'] ) {
			return $field_content;
		}
		$field_content = str_replace( 'name=', 'required name=', $field_content );
		return $field_content;
	}

	/**
	 * Add additional attributes
	 *
	 * @param string $form_tag Form tag.
	 * @param array  $form_data Form data.
	 */
	public function add_additional_attributes( $form_tag, $form_data ) {
		// form_tag is a string like "<form method='post' enctype='multipart/form-data'  id='gform_1'  action='/gravity-form/' data-formid='1' novalidate>"
		// we will add the action url using preg_replace.
		$form_tag = preg_replace( '/action=\'[^\']*\'/', 'action="' . $this->get_action_url() . '"', $form_tag );
		// remove the last character of the form_tag which is '>'.
		$form_tag                  = substr( $form_tag, 0, -1 );
		$confirmation_settings     = array_shift( $form_data['confirmations'] );
		$snap_form_notice_settings = array(
			'type' => $confirmation_settings['type'],
		);

		switch ( $confirmation_settings['type'] ) {
			case 'message':
				$snap_form_notice_settings['success_message'] = $confirmation_settings['message'];
				break;
			case 'page':
				$snap_form_notice_settings['redirect_url'] = get_permalink( $confirmation_settings['page'] );
				break;
			case 'redirect':
				$snap_form_notice_settings['redirect_url'] = $confirmation_settings['url'];
				break;
		}

		$form_tag .= ' data-static-snap-type="form" data-static-snap-form-type="gravity-forms" data-static-snap-form-notice-settings=\'' . wp_json_encode( $snap_form_notice_settings ) . '\'>';

		$form_tag .= $this->add_hidden_fields( $form_data );

		return $form_tag;
	}



	/**
	 * Add hidden field
	 *
	 * @param string $name  Field name.
	 * @param string $value Field value.
	 */
	private function add_hidden_field( $name, $value ) {
		return '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
	}

	/**
	 * Add hidden fields
	 *
	 * @param array $form_data Form data.
	 */
	public function add_hidden_fields( $form_data ) {

		$hidden_fields  = '';
		$hidden_fields .= $this->add_hidden_field( self::TOKEN_FIELD_NAME, $this->get_website_token() );
		$hidden_fields .= $this->add_hidden_field( self::FORM_NAME_FIELD_NAME, $form_data['title'] );
		$hidden_fields .= $this->add_hidden_field( self::FORM_ID_FIELD_NAME, $form_data['id'] );
		$hidden_fields .= $this->add_hidden_field( self::FORM_TYPE_FIELD_NAME, 'wpforms' );
		return $hidden_fields;
	}

	/**
	 * Get name
	 */
	public function get_name(): string {
		return 'gravity-forms';
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		$asset_file         = include STATIC_SNAP_PLUGIN_DIR . '/assets/js/gravity-forms.asset.php';
		$asset_dependencies = $asset_file['dependencies'];
		wp_enqueue_script( 'static-snap-gravity-forms', STATIC_SNAP_PLUGIN_URL . '/assets/js/gravity-forms.js', $asset_dependencies, $asset_file['version'], true );
	}
}
