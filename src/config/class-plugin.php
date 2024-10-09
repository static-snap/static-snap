<?php
/**
 * Plugin data which are used through the plugin, most of them are defined
 *
 * @package   StaticSnap
 */

declare(strict_types=1);

namespace StaticSnap\Config;

use StaticSnap\Constants\Filters;
use StaticSnap\Traits\Singleton;

/**
 * Plugin data which are used through the plugin, most of them are defined
 * by the root file meta data. The data is being inserted in each class
 * that extends the Base abstract class
 *
 * @see Base
 * @since 1.0.0
 */
final class Plugin {

	/**
	 * Singleton trait
	 */
	use Singleton;


	/**
	 * Plugin name
	 *
	 * @var string
	 */
	public const NAME = 'Static Snap';
	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	public const SLUG = 'static-snap';


	/**
	 * Table base name
	 *
	 * @var string
	 */
	public const TABLE_BASE_NAME = 'static_snap';

	/**
	 * Base name for actions and filters
	 * This is used to define the actions and filters
	 */
	public const BASE_NAME = 'static_snap';


	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public const VERSION = '1.0.0';

	/**
	 * Option group
	 *
	 * @var string
	 */
	public const OPTION_GROUP = 'static_snap_options';





	/**
	 * Returns a base64 URL for the svg for use in the menu.
	 *
	 * @param bool $base64 Whether or not to return base64'd output.
	 *
	 * @return string SVG icon.
	 */
	public function get_icon_svg( $base64 = true ) {
		$icon = STATIC_SNAP_PLUGIN_DIR . '/assets/images/icon.svg';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- This is a local file.
		$svg = file_get_contents( $icon );

		if ( $base64 ) {
			//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- This encoding is intended.
			return 'data:image/svg+xml;base64,' . base64_encode( $svg );
		}

		return $svg;
	}

	/**
	 * Init the plugin
	 *
	 * @return void
	 */
	public function init() {
		// add static snap cookie to detect if we are in the static site or not.
		add_filter( Filters::POST_URL_REMOTE_ARGS, array( $this, 'post_url_remote_args' ), 10, 2 );
	}

	/**
	 * Post URL Remote Args
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public function post_url_remote_args( array $args ): array {

		$cookie = new \WP_Http_Cookie(
			array(
				'name'    => 'static_snap_is_static',
				'value'   => 'true',
				'path'    => '/',
				'expires' => time() + ( 24 * 3600 ),

			)
		);
		if ( ! empty( $args['cookies'] ) && is_array( $args['cookies'] ) ) {
			$args['cookies'][] = $cookie;
		} else {
			$args['cookies'] = array( $cookie );
		}

		return $args;
	}
}
