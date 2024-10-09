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
		return $wpdb->prefix . $this->table;
	}

	/**
	 * Init table
	 *
	 * @return void
	 */
	protected function init_table() {

		global $wpdb;
		$table_name      = $wpdb->prefix . $this->table;
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = sprintf( $this->table_definition, $table_name, $charset_collate );

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );
	}

	/**
	 * Truncate the table.
	 */
	public function truncate() {
		global $wpdb;
		// phpcs:ignore
		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $this->table );
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

		$table_name = $this->get_table_name();
		// phpcs:ignore
		$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id );
		// phpcs:ignore
		$row   = $wpdb->get_row( $query, ARRAY_A );
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

		$table_name = $this->get_table_name();
		// phpcs:ignore
		$query = $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $id );
	    // phpcs:ignore
		return $wpdb->query( $query );
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

		$table_name = $this->get_table_name();
		// phpcs:ignore
		$query = $wpdb->prepare( "DELETE FROM $table_name WHERE $key = %d", $id );
	    // phpcs:ignore
		return $wpdb->query( $query );
	}

	/**
	 * Reset auto_increment
	 */
	public function reset_auto_increment() {
		global $wpdb;
		$table_name = $this->get_table_name();

		// phpcs:ignore
		$max_id = $wpdb->get_var( "SELECT MAX(id) FROM $table_name" );

		if ( null !== $max_id ) {
			// phpcs:ignore
			$query = $wpdb->prepare( "ALTER TABLE $table_name AUTO_INCREMENT = %d", $max_id + 1 );
			// phpcs:ignore
			$wpdb->query( $query );
		} else {
			// phpcs:ignore
			$wpdb->query( "ALTER TABLE $table_name AUTO_INCREMENT = 1" );
		}
	}

	/**
	 * Delete all
	 *
	 * @return mixed
	 */
	public function delete_all() {
		global $wpdb;
		$table_name = $this->get_table_name();
		// phpcs:ignore
		return $wpdb->query( "DELETE FROM $table_name" );
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

					// Use the official prepare() function to sanitize the value.
					return $wpdb->prepare( '%s', $value );
				},
				$values
			)
		);
	}
}
