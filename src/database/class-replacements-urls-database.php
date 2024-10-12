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
final class Replacements_URLS_Database extends Table {


	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = Plugin::TABLE_BASE_NAME . '_urls_replacements';

	/**
	 * Table definition
	 *
	 * @var string
	 */
	protected $table_definition = '
	CREATE TABLE %s (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		url VARCHAR(255) NOT NULL,
		url_hash CHAR(32) NOT NULL,
		replacement_url VARCHAR(255) NOT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY url_hash_index (url_hash)
		) %s;
	';


	/**
	 * Insert many URLs.
	 *
	 * @param array $urls The array of \StaticSnap\Deployment\URL_Replacement to insert.
	 */
	public function insert_many( array $urls ) {
		// Insert many urls.
		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}
		$query  = $wpdb->prepare(
			'INSERT IGNORE INTO %i
		(`url`, `url_hash`,`replacement_url`)
		VALUES ',
			$wpdb->prefix . $this->table
		);
		$values = array();
		foreach ( $urls as $url ) {
			$values[] = $wpdb->prepare(
				'(%s, %s, %s)',
				$url->get_url(),
				$url->get_url_hash(),
				$url->get_url_replacement()
			);
		}
		$query .= implode( ',', $values );

		$query .= ' ON DUPLICATE KEY UPDATE
		`replacement_url` = VALUES( `replacement_url` )
		';

		// phpcs:ignore WordPress.DB
		$wpdb->query( $query );
	}

	/**
	 * Insert a URL.
	 *
	 *  @param \StaticSnap\Deployment\URL_Replacement $url The URL to insert.
	 */
	public function insert( \StaticSnap\Deployment\URL_Replacement $url ) {
		$this->insert_many( array( $url ) );
	}
}
