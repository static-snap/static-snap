<?php
/**
 * Settings
 * This class is used to add StaticSnap menu in the admin menu in WordPress Dashboard
 *
 * @package StaticSnap
 */

namespace StaticSnap\Dashboard;

use StaticSnap\Base;
use StaticSnap\Config\Plugin;
use StaticSnap\Constants\Actions;
use StaticSnap\Traits\Renderable;

/**
 * This class is used to add StaticSnap menu in the admin menu in WordPress Dashboard
 */
final class Settings extends Base {
	use Renderable;

	/**
	 * Settings page slug
	 *
	 * @var string
	 */
	const SETTINGS_PAGE_SLUG = Plugin::SLUG . '-settings';


	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_menu', array( $this, 'init' ), 20 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {

		add_menu_page(
			Plugin::NAME, // Page title.
			Plugin::NAME, // Menu title.
			'manage_options', // Capability.
			Plugin::SLUG, // Menu slug.
			// Callback function.
			array( $this, 'render_settings' ),
			$this->plugin->get_icon_svg(),
			20
		);

		add_submenu_page(
			Plugin::SLUG,
			__( 'Dashboard', 'static-snap' ), // Page title.
			__( 'Dashboard', 'static-snap' ), // Menu title.
			'manage_options', // Capability.
			Plugin::SLUG, // Menu slug.
			// Callback function.
			array( $this, 'render_settings' )
		);

		add_submenu_page(
			Plugin::SLUG,
			__( 'Settings', 'static-snap' ), // Page title.
			__( 'Settings', 'static-snap' ), // Menu title.
			'manage_options', // Capability.
			Plugin::SLUG . '#/environments', // Menu slug.
			// Callback function.
			array( $this, 'render_settings' )
		);
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// check if admin bar is enabled.
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		$frontend_asset_file = include STATIC_SNAP_PLUGIN_DIR . '/assets/js/frontend.asset.php';

		wp_enqueue_script( 'static-snap-frontend', STATIC_SNAP_PLUGIN_URL . '/assets/js/frontend.js', $frontend_asset_file['dependencies'], $frontend_asset_file['version'], true );

		// load bundle.asset.php.
		$asset_file = include STATIC_SNAP_PLUGIN_DIR . '/assets/js/dashboard.asset.php';
		// load react app.
		wp_set_script_translations( 'static-snap-dashboard', 'static-snap', STATIC_SNAP_PLUGIN_DIR . '/languages' );

		wp_enqueue_script( 'static-snap-dashboard', STATIC_SNAP_PLUGIN_URL . '/assets/js/dashboard.js', $asset_file['dependencies'], $asset_file['version'], true );

		// add nouce to the script.
		wp_localize_script(
			'static-snap-dashboard',
			'StaticSnapDashboardConfig',
			array(
				'static_snap_api_url'     => $this->app->get_static_snap_api_url( '', 'frontend' ),
				'static_snap_website_url' => $this->app->get_static_snap_website_url( '', 'frontend' ),
			)
		);
		add_action(
			'admin_enqueue_scripts',
			function () {
				// if we are in static-snap dashboard page.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( empty( $_GET['page'] ) || 'static-snap' !== $_GET['page'] ) {
					return;
				}

				/**
				 * Remove forms style from wp-admin
				 */
				$wp_styles = wp_styles();
				wp_deregister_style( 'wp-admin' );
				wp_register_style( 'wp-admin', false, array( 'dashicons', 'common', /* 'forms',  */'admin-menu', 'dashboard', 'list-tables', 'edit', 'revisions', 'media', 'themes', 'about', 'nav-menus', 'widgets', 'site-icon', 'l10n' ), $wp_styles->default_version );

				do_action( Actions::ADMIN_REMOVE_CONFLICTING_STYLES, $wp_styles );
			},
			11
		);
	}


	/**
	 * Render general react app
	 *
	 * @return void
	 */
	public function render_settings() {

		$this->render();
	}
}
