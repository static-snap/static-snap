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
final class Term_URL extends URL {
	/**
	 * Post URL
	 *
	 * @var \WP_Term $term
	 */
	private $term;


	/**
	 * Constructor
	 *
	 * @param \WP_Term $term Post URL.
	 * @param string   $source Source.
	 */
	public function __construct( \WP_Term $term, $source = 'Term_URL::class' ) {
		$this->term = $term;
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

		return get_term_link( $this->term, $this->term->term_taxonomy_id );
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
	 * Get term
	 *
	 * @return \WP_Term
	 */
	public function get_term(): \WP_Term {
		return $this->term;
	}

	/**
	 * Get url type
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'term';
	}

	/**
	 * Get type reference id
	 *
	 * @return int | null
	 */
	public function get_type_reference_id() {
		return $this->term->term_taxonomy_id;
	}
}
