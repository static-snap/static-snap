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
	protected $table_definition = <<<EOD
	CREATE TABLE %s (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		type VARCHAR(20) NOT NULL,
		type_reference_id INT(11) NULL DEFAULT NULL,
		url VARCHAR(255) NOT NULL,
		url_hash CHAR(32) NOT NULL,
		retries TINYINT(4) NOT NULL DEFAULT 0,
		processed TINYINT(1) NOT NULL DEFAULT 0,
		processed_status TINYINT(4) DEFAULT 0,
		local_path VARCHAR(255) NULL DEFAULT NULL,
		local_path_destination VARCHAR(255) NULL DEFAULT NULL,
		indexed TINYINT(1) NOT NULL DEFAULT 0,
		deployed TINYINT(1) NOT NULL DEFAULT 0,
		status varchar(20) NOT NULL,
		priority TINYINT(4) NOT NULL DEFAULT 0,
		source VARCHAR(100) NOT NULL,
		last_modified DATETIME NULL DEFAULT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY url_hash_index (url_hash)
		) %s;
	EOD;


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
		$query  = sprintf(
			'INSERT IGNORE INTO `%s`
		(`type`,`type_reference_id`,`url`, `url_hash`,`local_path`,`last_modified` , `status`, `priority`,`source`, `processed_status`)
		VALUES ',
			$wpdb->prefix . $this->table
		);
		$values = array();
		foreach ( $urls as $url ) {
			$values[] = sprintf(
				'("%s",%s,"%s", "%s", %s, "%s","%s", %d, "%s", %d )',
				$url->get_type(),
				$url->get_type_reference_id() ? $url->get_type_reference_id() : 'NULL',
				$url->get_url(),
				$url->get_url_hash(),
				empty( $url->get_local_path() ) ? 'NULL' : sprintf( '"%s"', $url->get_local_path() ),
				$url->get_last_modified(),
				$url->get_status(),
				$url->get_priority(),
				$url->get_source(),
				0
			);
		}
		$query .= implode( ',', $values );
		$query .= ' ON DUPLICATE KEY UPDATE
		`status` = VALUES(status),
		`updated_at` = CURRENT_TIMESTAMP,
		`last_modified` = VALUES(last_modified),
		`processed` = `processed`,
		`retries` = 0,
		`priority` = LEAST(priority, VALUES(priority)),
		`source` = `source`,
		`processed_status` = `processed_status`
		';

		// phpcs:ignore WordPress.DB
		$wpdb->query( $query );
		return $query;
	}

	/**
	 * Get URLs
	 *
	 * @param string $type The type of URLs to get. Available types are 'all' | 'assets' | 'posts'.
	 * @param string $find_type The finder type count or all.
	 * @param int    $limit The number of URLs to get.
	 * @param int    $offset The offset to start from.
	 * @return array
	 */
	public function get_all( string $type = 'all', string $find_type = 'all', int $limit = 50, int $offset = 0 ): array {
		$default_where = '`processed` = 0';

		$extra_where = call_user_func( array( $this, 'get_' . $type . '_where' ), $default_where );
		$find_type   = call_user_func( array( $this, 'get_' . $find_type . '_find_type' ) );

		global $wpdb;
		$query = sprintf(
			'SELECT %s FROM `%s` WHERE  %s ORDER BY `priority` ASC, `created_at` ASC, `id` ASC LIMIT %d OFFSET %d',
			$find_type,
			$wpdb->prefix . $this->table,
			$extra_where,
			$limit,
			$offset
		);

		// phpcs:ignore WordPress.DB
		return $wpdb->get_results( $query, OBJECT );
	}

	/**
	 * Get all find type
	 *
	 * @return string
	 */
	private function get_all_find_type(): string {
		return '*';
	}
	/**
	 * Get count find type
	 *
	 * @return string
	 */
	private function get_count_find_type(): string {
		return 'COUNT(*) as count';
	}

	/**
	 * Get all where
	 *
	 * @param string $default_where The default where clause.
	 *
	 * @return string
	 */
	private function get_all_where( string $default_where ): string {
		return $default_where;
	}

	/**
	 * Get all where
	 *
	 * @param string $default_where The default where clause.
	 *
	 * @return string
	 */
	private function get_deployed_where( string $default_where ): string {
		return '`deployed` = 0';
	}
	/**
	 * Get indexed where
	 *
	 * @param string $default_where The default where clause.
	 *
	 * @return string
	 */
	private function get_indexed_where( string $default_where ): string {
		return '`indexed` = 0 AND `local_path_destination` LIKE "%.html"';
	}


	/**
	 * Get assets where regexp
	 *
	 * @return string
	 */
	private function get_content_assets_regexp(): string {
		$extensions = Assets::get_content_assets_extensions();

		$extensions = implode(
			' | ',
			array_map(
				function ( $ext ) {
					return sprintf( ' % s', $ext );
				},
				$extensions
			)
		);
		return '`url` REGEXP \'\\.(%s)$\'';
	}
	/**
	 * Get all assets where
	 *
	 * @param string $default_where The default where clause.
	 *
	 * @return string
	 */
	private function get_assets_where( string $default_where ): string {
		$regexp = $this->get_content_assets_regexp();
		return sprintf(
			'%s AND `local_path` IS NOT NULL',
			$default_where,
			$regexp
		);
	}

	/**
	 * Get all content assets where
	 *
	 * @param string $default_where The default where clause.
	 *
	 * @return string
	 */
	private function get_content_assets_where( string $default_where ): string {
		$regexp = $this->get_content_assets_regexp();
		return sprintf(
			'%s AND `local_path` IS NOT NULL AND %s',
			$default_where,
			$regexp
		);
	}

	/**
	 * Get all posts where
	 *
	 * @param string $default_where The default where clause.
	 *
	 * @return string
	 */
	private function get_posts_where( string $default_where ): string {
		return sprintf( '%s AND `local_path` IS NULL', $default_where );
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
		$query = sprintf(
			'UPDATE `%s` SET `processed` = 1, `processed_status` = %d %s WHERE `id` = %d',
			$wpdb->prefix . $this->table,
			$status,
			$local_path_destination ? sprintf( ', `local_path_destination` = "%s"', $local_path_destination ) : '',
			$id
		);
		// phpcs:ignore WordPress.DB
		$wpdb->query( $query );
	}

	/**
	 * Set deployed
	 *
	 * @param int $id The id of the URL to set as deployed.
	 */
	public function set_deployed( int $id ) {
		global $wpdb;
		$query = sprintf(
			'UPDATE `%s` SET `deployed` = 1 WHERE `id` = %d',
			$wpdb->prefix . $this->table,
			$id
		);
		// phpcs:ignore WordPress.DB
		$wpdb->query( $query );
	}

	/**
	 * Set indexed
	 *
	 * @param int $id The id of the URL to set as indexed.
	 */
	public function set_indexed( int $id ) {
		global $wpdb;
		$query = sprintf(
			'UPDATE `%s` SET `indexed` = 1 WHERE `id` = %d',
			$wpdb->prefix . $this->table,
			$id
		);
		// phpcs:ignore WordPress.DB
		$wpdb->query( $query );
	}

	/**
	 * Increase retries
	 *
	 * @param int $id The id of the URL to increase the retries.
	 * @return void
	 */
	public function increase_retries( int $id ) {
		global $wpdb;
		$query = sprintf(
			'UPDATE `%s` SET `retries` = `retries` + 1 WHERE `id` = %d',
			$wpdb->prefix . $this->table,
			$id
		);
		// phpcs:ignore WordPress.DB
		$wpdb->query( $query );
	}
}
