<?php
/**
 * Get posts
 *
 * @package StaticSnap
 */

namespace StaticSnap\Database;

use StaticSnap\Config\Plugin;
use StaticSnap\Traits\Singleton;
use StaticSnap\Environments\Environment;

/**
 * Get posts class
 */
final class Environments_Database extends Table {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = Plugin::TABLE_BASE_NAME . '_environments';

	/**
	 * Table definition
	 *
	 * @var string
	 */
	protected $table_definition = '
	CREATE TABLE %s (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		type VARCHAR(255) NOT NULL,
		destination_type VARCHAR(20) NOT NULL default \'relative\',
		destination_path TEXT NOT NULL default \'/\',
		settings TEXT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY name_index (name)
		) %s;
	';

	/**
	 * Get all environments
	 *
	 * @return array
	 */
	public function get_all() {
		global $wpdb;
		if ( ! $wpdb ) {
			return array();
		}

		$table_name = $this->get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i', $table_name ), ARRAY_A );
	}

	/**
	 * Get environment by id
	 *
	 * @param int $id The id of the environment.
	 *
	 * @return mixed
	 */
	public function get_by_id( int $id ) {
		$row = parent::get_by_id( $id );
		return Environment::from_array( $row, true );
	}

	/**
	 * Get environment by name
	 *
	 * @param string $name The name of the environment.
	 *
	 * @return mixed
	 */
	public function get_by_name( string $name ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return null;
		}

		$table_name = $this->get_table_name();

		$query = $wpdb->prepare( 'SELECT * FROM %i WHERE name = %s', $table_name, $name );
		// phpcs:ignore
		$row   = $wpdb->get_row( $query, ARRAY_A );
		if ( ! $row ) {
			return null;
		}

		return Environment::from_array( $row, true );
	}



	/**
	 * Insert environment
	 *
	 * @param Environment $environment The environment to insert.
	 * @return mixed
	 */
	public function insert( Environment $environment ) {
		// check if the environment name exists.
		$existing = $this->get_by_name( $environment->get_name() );
		if ( $existing ) {
			return array(
				'errors' => array(
					'name' => 'Name already exists',
				),

			);
		}
		global $wpdb;
		if ( ! $wpdb ) {
			return null;
		}

		$table_name = $this->get_table_name();
		// phpcs:ignore
		return $wpdb->insert( $table_name, $environment->to_array(true) );
	}



	/**
	 * Update environment
	 *
	 * @param int         $id   The id of the environment.
	 * @param Environment $environment The environment to update.
	 * @return mixed
	 */
	public function update( $id, Environment $environment ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return null;
		}
		$table_name = $this->get_table_name();

		// check if the environment name exists.
		$existing = $this->get_by_name( $environment->get_name() );
		if ( $existing && $existing->get_id() !== (int) $id ) {
			return array(
				'errors' => array(
					'name' => 'Name already exists',
				),
			);
		}

		// phpcs:ignore
		$updated = $wpdb->update( $table_name, $environment->to_array(true), array( 'id' => (int)$id ) );

		return $updated;
	}

	/**
	 * Delete environment by id
	 *
	 * @param int $id The id of the environment.
	 * @return mixed
	 */
	public function delete_by_id( $id ) {
		$environment = $this->get_by_id( $id );
		if ( ! $environment ) {
			return false;
		}
		$environment->delete_build_path();
		return parent::delete_by_id( $id );
	}
}
