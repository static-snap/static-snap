<?php
/**
 * Elementor Widget Base
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions\Elementor;

use Elementor\Widget_Base as Elementor_Widget_Base;



/**
 * Base widget class
 *
 * @since 1.0.0
 */
abstract class Widget_Base extends Elementor_Widget_Base {


	/**
	 * Widget version
	 *
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * Is preview mode
	 *
	 * Retrieve Elementor widget name.
	 *
	 * @var string
	 */
	protected $is_preview_mode = false;
	/**
	 * Load CSS
	 *
	 * @var boolean
	 */
	protected $load_css = true;
	/**
	 * Load JS
	 *
	 * @var boolean
	 */
	protected $load_js = false;
	/**
	 * Load JS Vendors
	 *
	 * @var array
	 */
	protected $load_js_vendors = array();

	/**
	 * Constructor
	 *
	 * @param array $data Data.
	 * @param array $args Args.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
		$this->is_preview_mode = \Elementor\Plugin::$instance->preview->is_preview_mode();
	}
	/**
	 * > It returns the full path of the file that contains the class definition
	 *
	 * @return string The full path of the file that the class is in.
	 */
	private function get_full_file_name() {
		$reflector = new \ReflectionClass( $this );
		return $reflector->getFileName();
	}
	/**
	 * > It returns the name of the file that contains the class definition
	 *
	 * @return string The name of the file that the class is in.
	 */
	private function get_file_name(): string {

		return basename( $this->get_full_file_name(), '.php' );
	}
	/**
	 * Get full file name uri
	 *
	 * @return string The full file name uri.
	 */
	public function get_full_file_name_uri() {
		$full_file_name = $this->get_full_file_name();
		// remove ROOT directory.
		return str_replace( rtrim( ABSPATH, '/' ), '', $full_file_name );
	}

	/**
	 * > This function registers a CSS using the wp_register_style function.
	 *
	 * @return array The name of the css.
	 */
	public function get_style_depends(): array {
		if ( ! $this->load_css ) {
			return array();
		}
		$filename = $this->get_file_name();
		$css_file = dirname( $this->get_full_file_name_uri() ) . "/$filename.css";

		wp_register_style( $filename, $css_file, array(), $this->version );

		return array( $filename );
	}
	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		if ( ! $this->load_js ) {
			return array();
		}
		$filename  = $this->get_file_name();
		$base_path = dirname( $this->get_full_file_name_uri() );

		$ret = array();

		foreach ( $this->load_js_vendors as $vendor => $file ) {
			// check if $file is a url.
			if ( ! filter_var( $file, FILTER_VALIDATE_URL ) ) {
				$file = $base_path . '/' . $file;
			}
			wp_register_script( $vendor, $file, array(), $this->version, true );
			$ret[] = $vendor;
		}

		$js_file = $base_path . "/$filename.js";
		wp_register_script( $filename, $js_file, $ret, $this->version, true );

		$ret[] = $filename;

		if ( $this->is_preview_mode && file_exists( ( dirname( $this->get_full_file_name() ) . '/editor-render.js' ) ) ) {
			wp_register_script( "$filename-preview", $base_path . '/editor-render.js', array( 'elementor-frontend', $filename ), $this->version, true );
			$ret[] = "$filename-preview";
		}

		return $ret;
	}


	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'static-snap' );
	}



	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Content', 'static-snap' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings  = $this->get_settings_for_display();
		$reflector = new \ReflectionClass( $this );
		$fn        = $reflector->getFileName();
		include dirname( $fn ) . '/render.php';
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @access protected
	 */
	protected function content_template() {
		$reflector = new \ReflectionClass( $this );
		$fn        = $reflector->getFileName();
		if ( ! file_exists( ( dirname( $fn ) . '/editor-render.php' ) ) ) {
			return null;
		}
		include dirname( $fn ) . '/editor-render.php';
	}
}
