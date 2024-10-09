<?php
/**
 * Get posts
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Config\Options;
use StaticSnap\Constants\Filters;

/**
 * Get posts class
 */
final class Posts {
	/**
	 * Default post filters, we will ignore attachments.
	 */
	public static function default_post_filters(): void {
		$build_options = Options::instance()->get( 'build_options' );
		add_filter(
			Filters::POST_TYPES,
			function ( $posts ) use ( $build_options ) {
				if ( ! isset( $build_options['enable_attachment_pages'] ) || ! $build_options['enable_attachment_pages'] ) {
					unset( $posts['attachment'] );
				}
				// elementor_library is a custom post type that is not public.
				unset( $posts['elementor_library'] );

				return $posts;
			}
		);
	}
}
