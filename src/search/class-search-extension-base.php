<?php
/**
 * Base class for Environments
 *
 * @package StaticSnap
 */

namespace StaticSnap\Search;

use StaticSnap\Config\Options;
use StaticSnap\Constants\Filters;
use StaticSnap\Extension\Extension_Base;

use function Sodium\add;

/**
 * This class is used to create the base environment.
 */
abstract class Search_Extension_Base extends Extension_Base {

	/**
	 * Is enabled
	 *
	 * @return boolean
	 */
	public function is_enabled(): bool {
		$search_options = Options::instance()->get(
			'search',
			array(
				'enabled' => false,
			)
		);

		return $search_options['enabled'] && $search_options['type'] === $this->get_name();
	}

	/**
	 * Get type
	 *
	 * Extension type
	 * Valids: 'search', 'environment_type'
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'search';
	}


	/**
	 * Get name.
	 *
	 * @return string
	 */
	abstract public function get_name(): string;

	/**
	 * Post to index
	 *
	 * @param \WP_Post $post Post object.
	 * @param string   $url URL.
	 * @return array Index. That is an array of objects with the following keys:
	 * objectID: unique identifier for the object.
	 * post_id: post ID.
	 * title: post title.
	 * content: post content.
	 * excerpt: post excerpt.
	 * url: post URL.
	 */
	public function post_to_index( $post, $url = null ) {
		$content = apply_filters( 'the_content', $post->post_content );
		$content = Content_Cleaner::remove_content_noise( $content );
		$content = wp_strip_all_tags( $content, false );
		$content = Content_Cleaner::remove_content_noise( $content );

		$title   = wp_kses_post( $post->post_title );
		$excerpt = wp_kses_post( self::get_the_excerpt( $post, $content ) );

		$index = array(
			array(
				'objectID'  => $post->ID,
				'post_id'   => $post->ID,
				'post_type' => $post->post_type,
				'title'     => $title,
				'content'   => $content,
				'excerpt'   => $excerpt,
				'url'       => $url ? $url : get_permalink( $post->ID ),
			),
		);

		// add filter to allow modification of index.
		$index = apply_filters( Filters::SEARCH_POST_TO_INDEX, $index, $post );

		return $index;
	}

	/**
	 *
	 * Prepare the index
	 * Called in Search_Prepare_Task
	 *
	 * @param string $build_path Build path.
	 */
	abstract public function prepare_index( $build_path );




	/**
	 * Index posts
	 *
	 * @param array  $posts Posts array.
	 * @param string $build_path Build path.
	 * @return void
	 */
	abstract public function index_posts( $posts, $build_path );

	/**
	 * Get the excerpt
	 *
	 * @param object $post Post object.
	 * @param string $content Post content.
	 * @return string
	 */
	private function get_the_excerpt( $post, $content ) {
		$text = get_the_excerpt( $post );

		if ( ! $text ) {
			$text = $content;
		}

		$generated_excerpt = wp_trim_words( $text, 55 );

		return apply_filters( 'get_the_excerpt', $generated_excerpt, $post );
	}
}
