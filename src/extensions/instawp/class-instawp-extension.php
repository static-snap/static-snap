<?php
/**
 * InstaWP
 *
 * @package StaticSnap
 */

declare(strict_types=1);


namespace StaticSnap\Extensions\InstaWP;

use StaticSnap\Config\Options;
use StaticSnap\Constants\Filters;

/**
 * InstaWP class
 */
final class InstaWP_Extension {
	/**
	 * Constructor
	 */
	public function __construct() {
		// detect if instawp is part of the domain.
		$domain              = wp_parse_url( get_site_url(), PHP_URL_HOST );
		$is_instawp_domain   = strpos( $domain, 'instawp' );
		$have_instawp_cookie = isset( $_COOKIE['instawp_skip_splash'] );
		// read options force instawp.
		$force_instawp = Options::instance()->get( 'force_instawp', false );

		$is_instawp = false !== $is_instawp_domain || $have_instawp_cookie || $force_instawp;

		// if its no an instawp domain or the cookie is set, return.
		if ( ! $is_instawp ) {
			return;
		}

		add_filter( Filters::POST_URL_REMOTE_ARGS, array( $this, 'post_url_remote_args' ), 1, 2 );
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
				'name'    => 'instawp_skip_splash',
				'value'   => 'true',
				'path'    => '/',
				'expires' => time() + ( 24 * 3600 ),

			)
		);

		if ( ! empty( $args['cookies'] ) ) {
			$args['cookies'][] = $cookie;
		} else {
			$args['cookies'] = array( $cookie );
		}

		return $args;
	}
}
