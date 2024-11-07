<?php
/**
 * URLS Database
 *
 * @package StaticSnap
 */

namespace StaticSnap\Database;

use StaticSnap\Config\Plugin;
use StaticSnap\Deployment\Assets;
use StaticSnap\Deployment\URL;
/**
 * URLS Database class
 */
final class URLS_Database extends Table {


	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = Plugin::TABLE_BASE_NAME . '_urls';

	const PROCESSED_STATUS_PENDING = 0;
	const PROCESSED_STATUS_SUCCESS = 1;
	const PROCESSED_STATUS_FAILED  = 2;

	/**
	 * Table definition
	 *
	 * @var string
	 */
	protected $table_definition = '
	CREATE TABLE %s (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		type VARCHAR(20) NOT NULL,
		type_reference_id INT(11) NULL DEFAULT NULL,
		url VARCHAR(255) NOT NULL,
		url_hash CHAR(32) NOT NULL,
		retries TINYINT(4) NOT NULL DEFAULT 0,
		processed TINYINT(1) NOT NULL DEFAULT 0,
		processed_status TINYINT(4) DEFAULT 0,
		local_path TEXT NULL DEFAULT NULL,
		local_path_destination TEXT NULL DEFAULT NULL,
		indexed TINYINT(1) NOT NULL DEFAULT 0,
		deployed TINYINT(1) NOT NULL DEFAULT 0,
		status VARCHAR(20) NOT NULL,
		priority TINYINT(4) NOT NULL DEFAULT 0,
		source VARCHAR(100) NOT NULL,
		last_modified DATETIME NULL DEFAULT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY url_hash_index (url_hash)
	) %s;
	';



	/**
	 * Insert many URLs.
	 *
	 * @param array $urls The array of URLs to insert.
	 */
	public function insert_many( array $urls ) {
		if ( empty( $urls ) ) {
			return;
		}
		// Insert many urls.
		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}
		$values = array();
		$this->start_fix_null_values_filter();
		foreach ( $urls as $url ) {
			$values[] = $url->get_type();
			$values[] = $url->get_type_reference_id() ? $url->get_type_reference_id() : 'NULL';
			$values[] = $url->get_url();
			$values[] = $url->get_url_hash();
			$values[] = $url->get_local_path() ? $url->get_local_path() : 'NULL';
			$values[] = $url->get_last_modified();
			$values[] = $url->get_status();
			$values[] = $url->get_priority();
			$values[] = $url->get_source();
			$values[] = 0;
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Reason: No relevant caches.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- Reason: Most performant way.
		$wpdb->query(
		// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Reason: we're passing an array instead.
			$wpdb->prepare(
				'INSERT IGNORE INTO %i
		(`type`,`type_reference_id`,`url`, `url_hash`,`local_path`,`last_modified` , `status`, `priority`,`source`, `processed_status`)
		VALUES ' . \implode( ', ', \array_fill( 0, \count( $urls ), '(%s,%s,%s, %s, %s, %s,%s, %d, %s, %d )' ) )
				. ' ON DUPLICATE KEY UPDATE
					`status` = VALUES(status),
					`updated_at` = CURRENT_TIMESTAMP,
					`last_modified` = VALUES(last_modified),
					`processed` = `processed`,
					`retries` = 0,
					`priority` = LEAST(priority, VALUES(priority)),
					`source` = `source`,
					`processed_status` = `processed_status`',
				\array_merge( array( $wpdb->prefix . $this->table ), $values )
			)
		);

		$this->end_fix_null_values_filter();
	}

	/**
	 * Get URLs
	 *
	 * @param string $type The type of URLs to get. Available types are 'all' | 'assets' | 'posts' | 'content_assets' | 'deployed' | 'indexed'.
	 * @param int    $limit The number of URLs to get.
	 * @param int    $offset The offset to start from.
	 * @return array
	 */
	public function get_all( string $type = 'all', int $limit = 50, int $offset = 0 ): array {
		global $wpdb;
		if ( ! $wpdb ) {
			return array();
		}
		$method = 'get_' . $type . '_results';
		// check if function exists.
		if ( ! method_exists( $this, $method ) ) {
			return array();
		}

		return call_user_func( array( $this, $method ), $limit, $offset );
	}

	/**
	 * Default get all
	 *
	 * @param int $limit The number of URLs to get.
	 * @param int $offset The offset to start from.
	 * @return array
	 */
	public function get_all_results( $limit, $offset ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return array();
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE `processed` = 0 ORDER BY `priority` ASC, `created_at` ASC, `id` ASC LIMIT %d OFFSET %d',
				$wpdb->prefix . $this->table,
				$limit,
				$offset
			),
			OBJECT
		);
	}

	/**
	 * Get Deployed results
	 *
	 * @param int $limit The number of URLs to get.
	 * @param int $offset The offset to start from.
	 * @return array
	 */
	public function get_deployed_results( $limit, $offset ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return array();
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE `deployed` = 0 ORDER BY `priority` ASC, `created_at` ASC, `id` ASC LIMIT %d OFFSET %d',
				$wpdb->prefix . $this->table,
				$limit,
				$offset
			),
			OBJECT
		);
	}


	/**
	 * Get Indexed results
	 *
	 * @param int $limit The number of URLs to get.
	 * @param int $offset The offset to start from.
	 * @return array
	 */
	public function get_indexed_results( $limit, $offset ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return array();
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE `indexed` = 0 AND `local_path_destination` LIKE %s ORDER BY `priority` ASC, `created_at` ASC, `id` ASC LIMIT %d OFFSET %d',
				$wpdb->prefix . $this->table,
				'%.html',
				$limit,
				$offset
			),
			OBJECT
		);
	}

	/**
	 * Get Assets results
	 *
	 * @param int $limit The number of URLs to get.
	 * @param int $offset The offset to start from.
	 * @return array
	 */
	public function get_assets_results( $limit, $offset ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return array();
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE `processed` = 0 AND `local_path` IS NOT NULL ORDER BY `priority` ASC, `created_at` ASC, `id` ASC LIMIT %d OFFSET %d',
				$wpdb->prefix . $this->table,
				$limit,
				$offset
			),
			OBJECT
		);
	}


	/**
	 * Get Content Assets results extensions string for query
	 *
	 * @return string
	 */
	private function get_content_assets_results_extensions_string_for_query(): string {
		$extensions = Assets::get_content_assets_extensions();

		$extensions = implode(
			'|',
			array_map(
				function ( $ext ) {
					return sprintf( '%s', $ext );
				},
				$extensions
			)
		);
		return $extensions;
	}

	/**
	 * Get Content Assets results
	 *
	 * @param int $limit The number of URLs to get.
	 * @param int $offset The offset to start from.
	 * @return array
	 */
	public function get_content_assets_results( $limit, $offset ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return array();
		}
		$extensions = $this->get_content_assets_results_extensions_string_for_query();

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE `processed` = 0 AND `local_path` IS NOT NULL AND `url` REGEXP %s ORDER BY `priority` ASC, `created_at` ASC, `id` ASC LIMIT %d OFFSET %d',
				$wpdb->prefix . $this->table,
				"\\.($extensions)$",
				$limit,
				$offset
			),
			OBJECT
		);
	}


	/**
	 * Get Post results
	 *
	 * @param int $limit The number of URLs to get.
	 * @param int $offset The offset to start from.
	 * @return array
	 */
	public function get_posts_results( $limit, $offset ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return array();
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE `processed` = 0 AND `local_path` IS NULL ORDER BY `priority` ASC, `created_at` ASC, `id` ASC LIMIT %d OFFSET %d',
				$wpdb->prefix . $this->table,
				$limit,
				$offset
			),
			OBJECT
		);
	}

	/**
	 * Set processed
	 *
	 * @param int    $id The id of the URL to set as processed.
	 * @param int    $status The status of the URL to set as processed.
	 *    0 = pending, 1 = success, 2 = failed.
	 * @param string $local_path_destination The local path destination.
	 */
	public function set_processed( int $id, int $status = 1, $local_path_destination = null ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}
		$destination_query = $local_path_destination ? ', `local_path_destination` = "%s"' : '';
		$query             = 'UPDATE %i SET `processed` = 1, `processed_status` = %d ' . $destination_query . ' WHERE `id` = %d';
		// phpcs:ignore WordPress.DB
		$prepared_query = $local_path_destination ? $wpdb->prepare( $query, $wpdb->prefix . $this->table, $status, $local_path_destination, $id ) : $wpdb->prepare( $query, $wpdb->prefix . $this->table, $status, $id );
		// phpcs:ignore WordPress.DB
		$wpdb->query( $prepared_query );
	}

		/**
		 * Set deployed
		 *
		 * @param int $id The id of the URL to set as deployed.
		 */
	public function set_deployed( int $id ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query(
			$wpdb->prepare( 'UPDATE %i SET `deployed` = 1 WHERE `id` = %d', $wpdb->prefix . $this->table, $id )
		);
	}

		/**
		 * Set indexed
		 *
		 * @param int $id The id of the URL to set as indexed.
		 */
	public function set_indexed( int $id ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare( 'UPDATE %i SET `indexed` = 1 WHERE `id` = %d', $wpdb->prefix . $this->table, $id ) );
	}

		/**
		 * Increase retries
		 *
		 * @param int $id The id of the URL to increase the retries.
		 * @return void
		 */
	public function increase_retries( int $id ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare( 'UPDATE %i SET `retries` = `retries` + 1 WHERE `id` = %d', $wpdb->prefix . $this->table, $id ) );
	}
}
