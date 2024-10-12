<?php
/**
 * Renderable
 *
 * @package StaticSnap
 */

declare(strict_types=1);

namespace StaticSnap\Traits;

use StaticSnap\Config\Plugin;
use StaticSnapVendor\Mustache_Engine;


/**
 * The renderable trait to render a view
 */
trait Renderable {
	/**
	 * Use template engine
	 *
	 * @var bool
	 */
	protected $keep_variables = false;
	/**
	 * Template file extension
	 *
	 * @var string
	 */
	protected $template_extension = 'html';
	/**
	 * Engine
	 *
	 * @var mixed
	 */
	protected $template_engine = null;


	/**
	 * Allowed HTML
	 *
	 * @var array
	 */
	protected $allowed_html = array();


	/**
	 * The directory where the templates are located.
	 *
	 * @var string
	 */
	protected $template_dir = 'views';
	/**
	 * The view to render.
	 *
	 * @var string|null
	 */
	protected $view = null;

	/**
	 * Reflection values for the Renderable trait.
	 *
	 * @var array|null
	 */
	private $reflection_values = null;


	/**
	 * Keep variables
	 * Default is false.
	 *
	 * @param bool $keep_variables If set to true, the template engine will not be used and variables will be kept.
	 */
	final public function set_keep_variables( bool $keep_variables ) {
		$this->keep_variables = $keep_variables;
	}
	/**
	 * Get the reflection values for the Renderable trait.
	 *
	 * @return array The reflection values for the Renderable trait.
	 */
	final protected function get_reflection_values(): array {
		if ( $this->reflection_values ) {
			return $this->reflection_values;
		}
		$reflection = new \ReflectionClass( $this );
		$class_dir  = dirname( $reflection->getFileName() );
		$class_name = $reflection->getShortName();

		$view = strtolower( $class_name );
		$view = str_replace( '_', '-', $view );

		$this->reflection_values = compact( 'reflection', 'class_dir', 'class_name', 'view' );
		return $this->reflection_values;
	}

	/**
	 * Init the template engine.
	 *
	 * @return void
	 */
	final protected function init_template_engine() {
		if ( $this->keep_variables ) {
			$this->template_engine = new class(){
				/**
				 * Render a template
				 *
				 * @param string $template The template to render.
				 * @return string The rendered template.
				 */
				public function render( $template ) {
					return $template;
				}
			};
			return;
		}

		$this->template_engine = new Mustache_Engine(
			array(
				'entity_flags' => ENT_QUOTES,
			)
		);
	}

	/**
	 * Get the template file path.
	 *
	 * @return string The template file path.
	 */
	final protected function get_template_file(): string {
		// get full path of the class that is using the trait!
		$reflection_values = $this->get_reflection_values();
		// check if the view is set.
		$view = $this->get_view();

		// get full path to the template directory using current file.
		$full_template_path = $reflection_values['class_dir'] . "/{$this->template_dir}/{$view}.{$this->template_extension}";

		return $full_template_path;
	}



	/**
	 * Get the view to render.
	 *
	 * @return string The view to render.
	 */
	final protected function get_view(): string {
		if ( $this->view ) {
			return $this->view;
		}
		// get full path of the class that is using the trait.
		$reflection_values = $this->get_reflection_values();

		return $reflection_values['view'];
	}

	/**
	 * Set the view to render.
	 *
	 * @param string $view The view to render.
	 * @return void
	 */
	final protected function set_view( string $view ): void {
		$this->view = $view;
	}

	/**
	 * Render a view
	 *
	 * @param array $data The data to pass to the view.
	 * @throws \InvalidArgumentException If the file does not exist.
	 *
	 * @return void
	 */
	final public function render( array $data = array(), $allowed_html = array() ): void {

		if ( ! $this->template_engine ) {
			$this->init_template_engine();
		}

		$__file            = $this->get_template_file();
		$reflection_values = $this->get_reflection_values();
		$class_name        = strtolower( $reflection_values['class_name'] );

		/**
		 * Filter the file before rendering.
		 * this is useful if you want to change the file path before rendering.
		 * For example, you can change the file path based on you theme or plugin.
		 *  Example:
		 * add_filter( "static_snap_search_result_template_file", function( $file ){ return "/mycustompath/mycustomfile.php" } );
		 */
		$__file = apply_filters( Plugin::BASE_NAME . "_{$class_name}_file", $__file );

		if ( ! file_exists( $__file ) ) {
			throw new \InvalidArgumentException( sprintf( 'Could not render.  The file %s could not be found', esc_html( $__file ) ) );

		}
		/**
		 * We have two modes
		 * php variables"
		 * <p><?php echo $variable ?></p>
		 * or
		 * mustache variables
		 * <p>{{variable}}</p>
		 *
		 * So, if we have templates that will be reused in javascript, we can use mustache variables.
		 */
		foreach ( $data as $key => $value ) {
			${$key} = $value;
		}
		ob_start();
		include $__file;
		$template = ob_get_clean();

		echo wp_kses(
			$this->template_engine->render( $template, $data ),
			array_merge(
				wp_kses_allowed_html( 'post' ),
				$this->allowed_html,
			),
			wp_allowed_protocols()
		);
	}
}
