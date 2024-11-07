<?php
/**
 * Database Base table
 *
 * @package StaticSnap
 */

namespace StaticSnap\Database;

use StaticSnap\Traits\Singleton;
use StaticSnap\Config\Plugin;
/**
 * URLS Database class
 */
abstract class Table {

	use Singleton;

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = Plugin::TABLE_BASE_NAME . '_urls';

	/**
	 * Table definition
	 *
	 * @var string
	 */
	protected $table_definition = '';




	/**
	 * Init
	 *
	 * @return void
	 */
	// phpcs:ignore
	protected function init() {
		$this->init_table();
	}

	/**
	 * Get table name
	 */
	public function get_table_name() {
		global $wpdb;
		if ( ! $wpdb ) {
			return null;
		}
		return $wpdb->prefix . $this->table;
	}

	/**
	 * Init table
	 *
	 * @return void
	 */
	protected function init_table() {

		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}
		$table_name      = $wpdb->prefix . $this->table;
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = sprintf( $this->table_definition, $table_name, $charset_collate );

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );
	}

	/**
	 * Drop the table.
	 */
	public function drop_table() {
		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}
		$table_name = $this->get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table_name ) );
	}

	/**
	 * Truncate the table.
	 */
	public function truncate() {
		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}

		$wpdb->query( $wpdb->prepare( 'TRUNCATE TABLE %i', $wpdb->prefix . $this->table ) );
	}


	/**
	 * Get  by id
	 *
	 * @param int $id The id of the item.
	 *
	 * @return mixed
	 */
	public function get_by_id( int $id ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return null;
		}

		$table_name = $this->get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', $table_name, $id ), ARRAY_A );

		if ( ! $row ) {
			return null;
		}

		return $row;
	}

	/**
	 * Delete environment by id
	 *
	 * @param int $id The id of the environment.
	 * @return mixed
	 */
	public function delete_by_id( $id ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return null;
		}

		$table_name = $this->get_table_name();

	    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE id = %d', $table_name, $id ) );
	}

	/**
	 * Delete  by key
	 *
	 * @param string $key The key to delete by.
	 * @param int    $id The id of the environment.
	 * @return mixed
	 */
	public function delete_by( $key, $id ) {
		global $wpdb;
		if ( ! $wpdb ) {
			return null;
		}

		$table_name = $this->get_table_name();
	    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE %i = %d', $table_name, $key, $id ) );
	}

	/**
	 * Reset auto_increment
	 */
	public function reset_auto_increment() {
		global $wpdb;
		if ( ! $wpdb ) {
			return;
		}
		$table_name = $this->get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$max_id = $wpdb->get_var( $wpdb->prepare( 'SELECT MAX(id) FROM %i', $table_name ) );

		if ( null !== $max_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %i AUTO_INCREMENT = %d', $table_name, $max_id + 1 ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %i AUTO_INCREMENT = 1', $table_name ) );
		}
	}

	/**
	 * Delete all
	 *
	 * @return mixed
	 */
	public function delete_all() {
		global $wpdb;
		if ( ! $wpdb ) {
			return null;
		}
		$table_name = $this->get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $table_name ) );
	}

	/**
	 * Prepare array for SQL IN clause
	 *
	 * @param array $values Values to prepare.
	 * @return string
	 */
	public static function prepare_array( $values ) {
		return implode(
			',',
			array_map(
				function ( $value ) {
					global $wpdb;
					if ( ! $wpdb ) {
						return $value;
					}

					// Use the official prepare() function to sanitize the value.
					return $wpdb->prepare( '%s', $value );
				},
				$values
			)
		);
	}

	/**
	 * Replace the 'NULL' string with NULL
	 *
	 * @param  string $query The query to replace the 'NULL' string with NULL.
	 * @return string $query
	 */
	public function wp_db_null_value( $query ) {
		return str_ireplace( "'NULL'", 'NULL', $query );
	}

	/**
	 * Fix null values filter
	 */
	protected function start_fix_null_values_filter() {
		add_filter( 'query', array( $this, 'wp_db_null_value' ) );
	}

	/**
	 * Remove null values filter
	 */
	protected function end_fix_null_values_filter() {
		remove_filter( 'query', array( $this, 'wp_db_null_value' ) );
	}
}
