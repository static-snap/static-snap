<?php
/**
 * Elementor Form Extension
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Forms\Elementor;

use StaticSnap\API\API;
use StaticSnap\Application;
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
	 * @param array                                    $_settings settings for display.
	 * @param \ElementorPro\Modules\Forms\Widgets\Form $form Form data.
	 */
	public function update_action( $_settings, $form ) {

		$form->add_render_attribute( 'form', 'data-static-snap-type', 'form' );
		$form->add_render_attribute( 'form', 'data-static-snap-form-type', 'elementor' );
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
	 * Get all post with forms
	 *
	 * @return array
	 */
	protected function get_all_post_with_forms() {
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return array();
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Reason: We need to use a direct query to get elementor forms.
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT wp_posts.ID, wp_posts.post_type, wp_posts.post_title, wp_postmeta.post_id, wp_postmeta.meta_value
				FROM %i AS wp_posts
				RIGHT JOIN %i AS wp_postmeta
				ON wp_posts.ID = wp_postmeta.post_id
				WHERE 
					wp_postmeta.meta_key = '_elementor_data' 
					AND wp_postmeta.meta_value LIKE %s 
					AND wp_posts.post_type != 'revision'
			",
				$wpdb->posts,
				$wpdb->postmeta,
				'%form_name%'
			)
		);
	}


	/**
	 * Elementor forms
	 *
	 * @var array
	 */
	public $elementor_forms = array();


	/**
	 * Find form elements.
	 *
	 * Recursively finds elements matching a specific type and widget type, storing their settings indexed by their ID.
	 *
	 * @param array  $elements     The array of elements to check.
	 * @param string $element_type The type of element to find.
	 * @param string $widget_type  The type of widget to find (default: 'form').
	 *
	 * @return array The form elements indexed by their IDs with their settings.
	 */
	public function find_form_elements( array $elements, string $element_type = 'widget', string $widget_type = 'form' ): array {
		if ( empty( $elements ) ) {
			return $this->elementor_forms;
		}

		foreach ( $elements as $element ) {
			// Check if the element matches the specified type and widget type.
			if ( $this->is_matching_element( $element, $element_type, $widget_type ) ) {
				// We save the settings with the element ID as the key.
				$this->elementor_forms[ $element['id'] ] = $element['settings'];
			} elseif ( $this->maybe_is_global_form_element( $element ) ) {
				$global_element_data = get_post_meta( $element['templateID'], '_elementor_data', true );
				if ( $global_element_data ) {
					$global_element_data = json_decode( $global_element_data, true );
					if ( ! empty( $global_element_data[0] ) ) {
						// We save the settings with the element ID as the key but with the global form settings.
						$this->elementor_forms[ $element['id'] ] = $global_element_data[0]['settings'];
					}
				}
			}

			// If the element has more elements, we need to check them as well.
			if ( ! empty( $element['elements'] ) ) {
				$this->find_form_elements( $element['elements'], $element_type, $widget_type );
			}
		}

		return $this->elementor_forms;
	}

	/**
	 * Check if an element matches the specified type and widget type.
	 *
	 * @param array  $element The element data container.
	 * @param string $element_type   The type of element to match.
	 * @param string $widget_type    The type of widget to match.
	 *
	 * @return bool True if the element matches; false otherwise.
	 */
	private function is_matching_element( array $element, string $element_type, string $widget_type ): bool {
		return isset( $element['elType'], $element['widgetType'], $element['id'], $element['settings'] )
		&& $element['elType'] === $element_type
		&& $element['widgetType'] === $widget_type;
	}

	/**
	 * Maybe is a global form element
	 *
	 * @param array $element The element data container.
	 */
	public function maybe_is_global_form_element( array $element ): bool {
		return isset( $element['settings']['form_name'] ) && ! empty( $element['settings']['form_name'] && 'global' === $element['widgetType'] && ! empty( $element['templateID'] ) );
	}



	/**
	 * Get all forms
	 */
	protected function get_all_forms() {
		$post_with_forms = $this->get_all_post_with_forms();
		$forms           = array();

		foreach ( $post_with_forms as $post ) {
			$metadata = \json_decode( $post->meta_value, true );
			$forms    = $this->find_form_elements( $metadata );
		}

		return $forms;
	}



	/**
	 * Sync forms settings
	 * This method is used to sync form settings with the static snap server
	 *
	 * @return bool True if the forms settings were synced successfully; false otherwise.
	 */
	public function sync_forms_settings() {
		if ( ! is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
			return false;
		}

		$forms                 = $this->get_all_forms();
		$website_form_settings = array();

		foreach ( $forms as $form_id => $form_settings ) {
			$email_settings    = array();
			$redirect_settings = null;
			$popup_settings    = array();
			$webhooks_settings = array();

			if ( ! empty( $form_settings['email_to'] ) && ! empty( $form_settings['submit_actions'] ) && in_array( 'email', $form_settings['submit_actions'], true ) ) {
				$email_settings[] =
					array(
						'to'        => $form_settings['email_to'] ?? null,
						'subject'   => $form_settings['email_subject'] ?? null,
						'from'      => $form_settings['email_from'] ?? null,
						'from_name' => $form_settings['email_from_name'] ?? null,
						'cc'        => $form_settings['email_cc'] ?? null,
						'bcc'       => $form_settings['email_bcc'] ?? null,
						'content'   => $form_settings['email_content'] ?? null,
					);
			}

			if ( ! empty( $form_settings['email_to_2'] ) && ! empty( $form_settings['submit_actions'] ) && in_array( 'email2', $form_settings['submit_actions'], true ) ) {
				$email_settings[] =
					array(
						'to'        => $form_settings['email_to_2'],
						'subject'   => $form_settings['email_subject_2'] ?? null,
						'from'      => $form_settings['email_from_2'] ?? null,
						'from_name' => $form_settings['email_from_name_2'] ?? null,
						'cc'        => $form_settings['email_cc_2'] ?? null,
						'bcc'       => $form_settings['email_bcc_2'] ?? null,
						'content'   => $form_settings['email_content_2'] ?? null,

					);

			}

			if ( ! empty( $form_settings['redirect_to'] ) && ! empty( $form_settings['submit_actions'] ) && in_array( 'redirect', $form_settings['submit_actions'], true ) ) {
				$redirect_settings = $form_settings['redirect_to'] ?? null;
			}
			if ( ! empty( $form_settings['popup_action'] ) && ! empty( $form_settings['submit_actions'] ) && in_array( 'popup', $form_settings['submit_actions'], true ) ) {
				$popup_settings = array(
					'action'   => $form_settings['popup_action'] ?? null,
					'popup_id' => $form_settings['popup_action_popup_id'] ?? null,
				);
			}
			if ( ! empty( $form_settings['webhooks'] ) && ! empty( $form_settings['submit_actions'] ) && in_array( 'webhook', $form_settings['submit_actions'], true ) ) {
				$webhooks_settings[] = array(
					'url'     => $form_settings['webhooks'] ?? null,
					'options' => array(
						'webhooks_advanced_data' => $form_settings['webhooks_advanced_data'] && 'yes' === $form_settings['webhooks_advanced_data'],
					),
				);
			}

			$website_form_settings[] = array(
				'website_form_website_id'     => Application::instance()->get_wp_installation_md5(),
				'website_form_name'           => $form_settings['form_name'],
				'website_form_id'             => $form_id,
				'webiste_form_extension_name' => $this->get_name(),
				'website_form_settings'       => array(
					'submit_actions' => $form_settings['submit_actions'] ?? null,
					'email'          => $email_settings,
					'redirect_to'    => $redirect_settings,
					'popup'          => $popup_settings,
					'webhooks'       => $webhooks_settings,
					'messages'       => array(
						'success'  => $form_settings['success_message'] ?? null,
						'error'    => $form_settings['error_message'] ?? null,
						'required' => $form_settings['required_field_message'] ?? null,
						'invalid'  => $form_settings['invalid_message'] ?? null,
					),
				),
			);
		}

		// Send the forms to the server. If we send an empty array, the server will remove all forms settings;
		// If we send an array with forms, the server will update the forms settings.
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
