<?php
/**
 * Frontend
 *
 * @package StaticSnap
 */

namespace StaticSnap\Frontend;

use StaticSnap\Config\Options;
use StaticSnap\Config\Plugin;
use StaticSnap\Constants\Filters;
use StaticSnap\Search\Search_Result_Template;


/**
 * Frontend class
 */
final class Frontend {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		// add template to bottom of the page.
		add_action( 'wp_footer', array( $this, 'search_results_template' ), 10 );
	}

	/**
	 * Search template
	 */
	public function search_results_template() {
		$template = new Search_Result_Template();
		// Keep variables.
		$template->set_keep_variables( true );
		// phpcs:ignore
		echo $template->render();
	}

	/**
	 * Check if the site is static
	 *
	 * @return bool
	 */
	public static function is_static() {
		// check if is set the static_snap_is_static cookie.
		if ( isset( $_COOKIE['static_snap_is_static'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the site has translations
	 *
	 * @return bool
	 */
	public static function has_translations() {
		return apply_filters( Filters::FRONTEND_HAS_TRANSLATIONS, false );
	}



	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		$asset_file = include STATIC_SNAP_PLUGIN_DIR . '/assets/js/frontend.asset.php';

		wp_enqueue_style( 'static-snap-frontend', STATIC_SNAP_PLUGIN_URL . '/assets/css/frontend.css', array(), $asset_file['version'] );
		wp_enqueue_script( 'static-snap-frontend', STATIC_SNAP_PLUGIN_URL . '/assets/js/frontend.js', $asset_file['dependencies'], $asset_file['version'], true );

		$options = Options::instance()->get_options( true );
		// remove connect options.
		unset( $options['connect'] );

		$frontend_localize_data = array(
			'has_translations'     => self::has_translations() ? 'true' : 'false',
			'is_static'            => self::is_static() ? 'true' : 'false',
			'options'              => $options,
			'locale'               => get_locale(),
			'is_admin_bar_showing' => is_admin_bar_showing() ? 'true' : 'false',

		);

		$frontend_localize_data = apply_filters( Filters::FRONTEND_LOCALIZE_DATA, $frontend_localize_data );

		wp_localize_script(
			'static-snap-frontend',
			'StaticSnapFrontendConfig',
			$frontend_localize_data
		);

		wp_set_script_translations( 'static-snap-frontend', 'static-snap' );
	}
}
