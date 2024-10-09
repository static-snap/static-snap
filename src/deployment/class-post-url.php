<?php
/**
 * Class Post URL
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

use StaticSnap\Constants\Filters;


/**
 * Post URL class
 */
final class Post_URL extends URL {

	/**
	 * Post URL
	 *
	 * @var \WP_Post $post
	 */
	private $post;


	/**
	 * Constructor
	 *
	 * @param \WP_Post $post Post URL.
	 * @param string   $source Source.
	 */
	public function __construct( \WP_Post $post, $source = 'Post_URL::class' ) {
		$this->post = $post;
		$this->set_source( $source );
	}


	/**
	 * Get priority
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 10;
	}

	/**
	 * Get post URL
	 *
	 * @return string
	 */
	public function get_url(): string {
		$url = '';
		switch ( $this->post->post_type ) {
			case 'page':
				$url = get_page_link( $this->post->ID );
				break;
			case 'attachment':
				$url = wp_get_attachment_url( $this->post->ID );
				break;
			default:
				$url = get_permalink( $this->post->ID );
				break;
		}

		return apply_filters( Filters::POST_URL, $url, $this->post );
	}



	/**
	 * Get last modified
	 *
	 * @return string
	 */
	public function get_last_modified(): string {

		return $this->post->post_modified;
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function get_status(): string {
		return $this->post->post_status;
	}


	/**
	 * Get post object
	 * Returns the post object.
	 *
	 * @return object
	 */
	public function get_post(): object {
		return $this->post;
	}

	/**
	 * Get url type
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'post';
	}

	/**
	 * Get type reference id
	 *
	 * @return int | null
	 */
	public function get_type_reference_id() {
		return $this->post->ID;
	}
}
