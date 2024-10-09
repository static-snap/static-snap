<?php
/**
 * Search widget
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Elementor\Widgets;

use StaticSnap\Extensions\Elementor\Widget_Base;

use Elementor\Controls_Manager;


/**
 * Search widget
 */
final class Search_Widget extends Widget_Base {
	/**
	 * Get widget version.
	 *
	 * Retrieve Elementor widget version.
	 *
	 * @var string
	 */
	protected $version = '0.1.0';

	/**
	 * Load js
	 *
	 * @var bool
	 */
	protected $load_js = true;


	/**
	 * Get widget name.
	 *
	 * Retrieve Elementor widget name.
	 *
	 * @var string
	 */
	public function get_title() {
		return __( 'Search', 'static-snap' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Elementor widget icon.
	 *
	 * @var string
	 */
	public function get_icon() {
		return 'eicon-search';
	}

	/**
	 * Get name
	 */
	public function get_name() {
		return 'staticsnap-search';
	}

	/**
	 * Register widget controls
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Content', 'static-snap' ),
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'label'     => esc_html__( 'Placeholder', 'static-snap' ),
				'type'      => Controls_Manager::TEXT,
				'separator' => 'before',
				'default'   => esc_html__( 'Search', 'static-snap' ) . '...',
				'dynamic'   => array(
					'active' => true,
				),
			)
		);

		$this->add_control(
			'size',
			array(
				'label'      => esc_html__( 'Size', 'static-snap' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'custom' ),
				'default'    => array(
					'size' => 50,
				),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-search-form__container' => 'min-height: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .elementor-search-form__submit' => 'min-width: {{SIZE}}{{UNIT}}',
					'body:not(.rtl) {{WRAPPER}} .elementor-search-form__icon' => 'padding-left: calc({{SIZE}}{{UNIT}} / 3)',
					'body.rtl {{WRAPPER}} .elementor-search-form__icon' => 'padding-right: calc({{SIZE}}{{UNIT}} / 3)',
					'{{WRAPPER}} .elementor-search-form__input, {{WRAPPER}}.elementor-search-form--button-type-text .elementor-search-form__submit' => 'padding-left: calc({{SIZE}}{{UNIT}} / 3); padding-right: calc({{SIZE}}{{UNIT}} / 3)',
				),

				'separator'  => 'before',
			)
		);

		$this->end_controls_section();
	}
}
