<?php
/**
 * Elementor Form Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Forms\Elementor;

use StaticSnap\Forms\Form_Extension_Base;


/**
 * Elementor Form Extension
 */
final class Elementor_Form_Extension extends Form_Extension_Base {


	private const TOKEN_FIELD_ID     = 'static-snap-website-token';
	private const FORM_NAME_FIELD_ID = 'static-snap-form-name';
	private const FORM_ID_FIELD_ID   = 'static-snap-form-id';
	private const FORM_TYPE_FIELD_ID = 'static-snap-form-type';

	/**
	 * Constructor
	 *
	 * @param array $params Parameters.
	 */
	public function __construct( $params = array() ) {
		parent::__construct( $params );
		if ( ! is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
			return;
		}
		if ( ! $this->is_enabled() ) {
			return;
		}

		// add elementor-form.js to the footer on frontend.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		// add action for form pre-render.
		add_action( 'elementor-pro/forms/pre_render', array( $this, 'update_action' ), 90, 2 );
		// before render.
		add_action( 'elementor/frontend/before_render', array( $this, 'before_render' ), 10, 1 );
		add_filter( 'elementor_pro/forms/render/item', array( $this, 'filter_static_snap_input_name' ), 10, 3 );
	}



	/**
	 * Filter static snap input name
	 *
	 * @param array                                    $item       The field value.
	 * @param int                                      $item_index The field index.
	 * @param \ElementorPro\Modules\Forms\Widgets\Form $form       An instance of the form.
	 */
	public function filter_static_snap_input_name( $item, $item_index, $form ) {
		$fields = array(
			self::TOKEN_FIELD_ID,
			self::FORM_NAME_FIELD_ID,
			self::FORM_ID_FIELD_ID,
			self::FORM_TYPE_FIELD_ID,
		);
		$names  = array(
			self::TOKEN_FIELD_NAME,
			self::FORM_NAME_FIELD_NAME,
			self::FORM_ID_FIELD_NAME,
			self::FORM_TYPE_FIELD_NAME,
		);

		if ( in_array( $item['_id'], $fields, true ) ) {
			// remove the old render attribute.
			$form->remove_render_attribute( 'input' . $item_index, 'name' );
			$form->add_render_attribute( 'input' . $item_index, 'name', $names[ array_search( $item['_id'], $fields, true ) ] );
		}

		return $item;
	}

	/**
	 * Before render
	 *
	 * @param \Elementor\Element_Base $element Element.
	 */
	public function before_render( $element ) {

		if ( 'form' === $element->get_name() ) {
			$settings = $element->get_settings();

			$settings['form_fields'][] = array(
				'_id'            => self::TOKEN_FIELD_ID,
				'field_type'     => 'hidden',
				'field_label'    => self::TOKEN_FIELD_ID,
				'custom_id'      => self::TOKEN_FIELD_ID,
				'required'       => true,
				'field_value'    => $this->get_website_token(),
				'allow_multiple' => false,
				'placeholder'    => '',

			);
			$settings['form_fields'][] = array(
				'_id'            => self::FORM_NAME_FIELD_ID,
				'field_type'     => 'hidden',
				'field_label'    => self::FORM_NAME_FIELD_ID,
				'custom_id'      => self::FORM_NAME_FIELD_ID,
				'required'       => true,
				'field_value'    => $settings['form_name'] ?? '',
				'allow_multiple' => false,
				'placeholder'    => '',
			);
			$settings['form_fields'][] = array(
				'_id'            => self::FORM_ID_FIELD_ID,
				'field_type'     => 'hidden',
				'field_label'    => self::FORM_ID_FIELD_ID,
				'custom_id'      => self::FORM_ID_FIELD_ID,
				'required'       => true,
				'field_value'    => $element->get_id() ?? '',
				'allow_multiple' => false,
				'placeholder'    => '',
			);

			$settings['form_fields'][] = array(
				'_id'            => self::FORM_TYPE_FIELD_ID,
				'field_type'     => 'hidden',
				'field_label'    => self::FORM_TYPE_FIELD_ID,
				'custom_id'      => self::FORM_TYPE_FIELD_ID,
				'required'       => true,
				'field_value'    => $this->get_name(),
				'allow_multiple' => false,
				'placeholder'    => '',
			);

			$element->set_settings( $settings );

		}
	}

	/**
	 * Update action
	 *
	 * @param array                                    $settings settings for display.
	 * @param \ElementorPro\Modules\Forms\Widgets\Form $form Form data.
	 */
	public function update_action( $settings, $form ) {

		$notice_settings = array(
			'type'                   => 'message',
			'success_message'        => $settings['success_message'],
			'error_message'          => $settings['error_message'],
			'required_field_message' => $settings['required_field_message'],
			'invalid_message'        => $settings['invalid_message'],
		);

		// remove empty values.
		$notice_settings = array_filter( $notice_settings );

		if ( is_array( $settings['submit_actions'] ) && array_search( 'redirect', $settings['submit_actions'], true ) ) {
			$notice_settings['type']         = 'redirect';
			$notice_settings['redirect_url'] = $settings['redirect_to'];

		}

		$form->add_render_attribute( 'form', 'data-static-snap-type', 'form' );
		$form->add_render_attribute( 'form', 'data-static-snap-form-type', 'elementor' );
		$form->add_render_attribute( 'form', 'data-static-snap-form-notice-settings', wp_json_encode( $notice_settings ) );
		$form->add_render_attribute( 'form', 'action', $this->get_action_url() );
	}

	/**
	 * Get name
	 */
	public function get_name(): string {
		return 'elementor-form';
	}

	/**
	 * Get Settings fields
	 */
	public function get_settings_fields(): array {
		return array(
			'elementor_form' => array(
				'type'    => 'text',
				'label'   => __( 'Elementor Form ID', 'static-snap' ),
				'default' => '',
			),
		);
	}
	/**
	 * Get type
	 */
	public function get_type(): string {
		return 'form';
	}

	/**
	 * Is configured
	 */
	public function is_configured(): bool {
		// phpcs:ignore
		// $elementor_form = $this->get_setting( 'elementor_form' );
		// phpcs:ignore
		// return ! empty( $elementor_form );
		return true;
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		$asset_file         = include STATIC_SNAP_PLUGIN_DIR . '/assets/js/elementor-forms.asset.php';
		$asset_dependencies = $asset_file['dependencies'];
		wp_enqueue_script( 'static-snap-elemntor-forms', STATIC_SNAP_PLUGIN_URL . '/assets/js/elementor-forms.js', $asset_dependencies, $asset_file['version'], true );
	}
}
