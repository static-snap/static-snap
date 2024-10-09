<?php
/**
 * Github Cache
 *
 * @package StaticSnap
 */

namespace StaticSnap\Cache;

/**
 * Class to manage cache.
 */
abstract class Cache_Persister {
	/**
	 * Cache
	 *
	 * @var array
	 */
	private $cache = array();

	/**
	 * Cache persist duration
	 *
	 * @var int
	 */
	private $cache_duration = HOUR_IN_SECONDS;

	/**
	 * Cache key
	 *
	 * @var string
	 */
	private $cache_key = '';

	/**
	 * Constructor for Github_Cache class.
	 *
	 * @param int $duration Cache duration.
	 */
	public function __construct( $duration = HOUR_IN_SECONDS ) {
		$this->cache_duration = $duration;
		$this->cache_key      = strtolower( str_replace( '\\', '_', get_class( $this ) ) );
		$this->load_cache();
	}

	/**
	 * Destructor for Github_Cache class.
	 */
	public function __destruct() {
		$this->persist_cache();
	}

	/**
	 * Get cache
	 *
	 * @param string $key Cache key.
	 * @return mixed
	 */
	public function get_cache_for_key( $key ) {
		if ( isset( $this->cache[ $key ] ) ) {
			return $this->cache[ $key ];
		}
		return false;
	}

	/**
	 * Get cache
	 *
	 * @param array $params Cache params will be used to generate cache key.
	 *
	 * @return array
	 */
	public function get_cache( $params = array() ) {
		$args_as_string = implode( '_', $params );
		// get function caller.
		// phpcs:ignore
		$caller = debug_backtrace()[1]['class'] . '::' . debug_backtrace()[1]['function'] ;
		if ( empty( $args_as_string ) ) {
			$caller .= '_' . $args_as_string;
		}
		return $this->get_cache_for_key( $caller );
	}

	/**
	 * Set cache
	 *
	 * @param string $key Cache key.
	 * @param mixed  $value Cache value.
	 * @return void
	 */
	public function set_cache_for_key( $key, $value ) {
		$this->cache[ $key ] = $value;
	}

	/**
	 * Set cache
	 *
	 * @param mixed $value Cache value.
	 * @param array $params Cache params will be used to generate cache key.
	 * @return void
	 */
	public function set_cache( $value, $params = array() ) {

		$args_as_string = implode( '_', $params );
		// phpcs:ignore
		$caller = debug_backtrace()[1]['class'] . '::' . debug_backtrace()[1]['function'];
		if ( empty( $args_as_string ) ) {
			$caller .= '_' . $args_as_string;
		}
		$this->set_cache_for_key( $caller, $value );
	}

	/**
	 * Delete cache
	 *
	 * @param string $key Cache key.
	 * @return void
	 */
	public function delete_cache_for_key( $key ) {
		if ( isset( $this->cache[ $key ] ) ) {
			unset( $this->cache[ $key ] );
		}
	}
	/**
	 * Delete cache
	 *
	 * @return void
	 */
	public function delete_cache() {
				// get function caller.
		// phpcs:ignore
		$params         = debug_backtrace()[1]['args'];
		$args_as_string = implode( '_', $params );
		// phpcs:ignore
		$caller = debug_backtrace()[1]['class'] . '::' . debug_backtrace()[1]['function'] . '_' . $args_as_string;
		$this->delete_cache_for_key( $caller );
	}

	/**
	 * Clear cache
	 *
	 * @return void
	 */
	public function clear_cache() {
		$this->cache = array();
	}

	/**
	 * Get cache keys
	 *
	 * @return array
	 */
	public function get_cache_keys() {
		return array_keys( $this->cache );
	}

	/**
	 * Persist cache
	 */
	public function persist_cache() {
		$cache = $this->cache;
		$cache = wp_json_encode( $cache );
		// set a transient with the cache expires in 1 hour.
		set_transient( $this->cache_key, $cache, $this->cache_duration );
	}

	/**
	 * Load cache
	 */
	public function load_cache() {
		$cache = get_transient( $this->cache_key );
		if ( $cache ) {
			$this->cache = json_decode( $cache, true );
		}
	}
}
