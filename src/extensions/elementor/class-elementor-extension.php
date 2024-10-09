<?php
/**
 * Class Elementor
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Elementor;

use StaticSnap\Frontend\Frontend;
use StaticSnap\Extensions\Elementor\Widgets\All_Widgets;

/**
 * Elementor class
 */
final class Elementor_Extension {

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'replace_elementor_settings_urls' ), 10 );
			add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_categories' ) );
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
		}
	}

	/**
	 * Replace Elementor settings URLs
	 */
	public function replace_elementor_settings_urls() {
		if ( ! Frontend::is_static() ) {
			return;
		}
	}

	/**
	 * Add Elementor widget categories
	 */
	public function add_elementor_widget_categories() {
		\Elementor\Plugin::instance()->elements_manager->add_category(
			'static-snap',
			array(
				'title' => 'Static Snap',
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Register widgets
	 */
	public function register_widgets() {

		foreach ( All_Widgets::ALL as $widget ) {
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new $widget() );
		}
	}
}
