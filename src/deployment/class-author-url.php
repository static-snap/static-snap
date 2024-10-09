<?php
/**
 * Class Post URL
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment;

/**
 * Post URL class
 */
final class Author_URL extends URL {
	/**
	 * Post URL
	 *
	 * @var \WP_User $author
	 */
	private $author;


	/**
	 * Constructor
	 *
	 * @param \WP_User $author Post URL.
	 * @param string   $source Source.
	 */
	public function __construct( \WP_User $author, $source = 'Term_URL::class' ) {
		$this->author = $author;
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

		return get_author_posts_url( $this->author->ID, $this->author->user_nicename );
	}



	/**
	 * Get last modified
	 *
	 * @return string
	 */
	public function get_last_modified(): string {

		return current_time( 'mysql' );
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function get_status(): string {
		return 'published';
	}

	/**
	 * Get author
	 *
	 * @return \WP_User
	 */
	public function get_author(): \WP_User {
		return $this->author;
	}

	/**
	 * Get url type
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'author';
	}
	/**
	 * Get type reference id
	 *
	 * @return int | null
	 */
	public function get_type_reference_id() {
		return $this->author->ID;
	}
}
