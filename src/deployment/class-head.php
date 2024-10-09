<?php
/**
 * Class Header
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Config\Options;
use StaticSnap\Constants\Actions;
use StaticSnap\Traits\Singleton;

/**
 * Header class
 *
 * This class will remove unneeded head links like Really Simple Discovery service endpoint.
 */
final class Head {
	use Singleton;



	/**
	 * Init
	 */
	public function init() {
		add_action( 'init', array( $this, 'remove_head_links' ) );
	}


	/**
	 * Remove head links
	 */
	public function remove_head_links() {
		// Really Simple Discovery service endpoint is not needed. It is xmlrpc.php endpoint.
		remove_action( 'wp_head', 'rsd_link' );
		$shortlinks_enabled = Options::instance()->get( 'build_options.enable_shortlinks', false );
		if ( $shortlinks_enabled ) {
			add_filter( 'get_shortlink', array( $this, 'nice_shortlink' ), 10, 3 );

		} else {
			// remove shortlink.
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		}

		do_action( Actions::REMOVE_HEAD_LINKS );
	}




	/**
	 * Nice shortlink
	 *
	 * @param string $shortlink Shortlink.
	 * @param int    $id        ID.
	 * @param string $context   Context.
	 * @return string
	 */
	public function nice_shortlink( $shortlink, $id, $context/*, $allow_slugs  */ ) {
		if ( empty( $shortlink ) ) {
			return $shortlink;
		}
		$post_id = 0;

		if ( 'query' === $context && is_singular() ) {
			$post_id = get_queried_object_id();
		} elseif ( 'post' === $context ) {
			if ( '' === $id ) {
				return '';
			}
			$post = get_post( $id );
			if ( ! empty( $post->ID ) ) {
				$post_id = $post->ID;
			}
		}
		if ( empty( $post_id ) ) {
			return $shortlink;
		}

		// base36 encode the post id.
		$base_36_post_id = base_convert( $post_id, 10, 36 );
		// nice post type.
		$post_type       = get_post_type( $post_id );
		$short_post_type = strlen( $post_type ) > 1 ? $post_type[0] . $post_type[ strlen( $post_type ) - 1 ] : $post_type;

		$shortlink = home_url( '/' ) . $short_post_type . $base_36_post_id;

		return $shortlink;
	}
}
