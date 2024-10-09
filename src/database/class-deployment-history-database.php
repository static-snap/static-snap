<?php
/**
 * Deployment Process
 *
 * @package StaticSnap
 */

namespace StaticSnap\Database;

use StaticSnap\Config\Plugin;

/**
 * Deployment Process
 */
final class Deployment_History_Database extends Table {


	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = Plugin::TABLE_BASE_NAME . '_deployment_history';
	/**
	 * Table definition
	 *
	 * @var string
	 */
	protected $table_definition = <<<EOD
	CREATE TABLE %s (
		id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		environment_id INT(11) UNSIGNED NULL DEFAULT NULL,
		status TINYINT(4) NOT NULL DEFAULT 0,
		start_time INT(11) NULL DEFAULT NULL,
		end_time INT(11) NULL DEFAULT NULL,
		error TEXT NULL DEFAULT NULL,
		status_information TEXT NULL DEFAULT NULL,
		created_by INT(11) UNSIGNED NULL DEFAULT NULL,
		PRIMARY KEY  (id)
		) %s;
	EOD;


	public const RUNNING   = 1;
	public const COMPLETED = 2;
	public const CANCELED  = 3;
	public const PAUSED    = 4;
	public const FAILED    = 5;
	// that means the deployment is done, but to be completed, the user needs to be notified.
	public const DONE = 6;



	/**
	 *  Get select
	 *
	 * @return string
	 */
	private function get_select() {
		global $wpdb;
		$table_name         = $this->get_table_name();
		$environments_table = Environments_Database::instance()->get_table_name();
		$wp_users_table     = $wpdb->prefix . 'users';

		return <<<EOD
			SELECT $table_name.*,
					$environments_table.name as environment_name, $environments_table.type as environment_type, $environments_table.settings as environment_settings,
					$wp_users_table.display_name as created_by_name, $wp_users_table.user_email as created_by_email
				FROM $table_name
				LEFT JOIN $environments_table ON $table_name.environment_id = $environments_table.id
				LEFT JOIN $wp_users_table     ON $table_name.created_by = $wp_users_table.ID
		EOD;
	}

	/**
	 * Get all history
	 */
	public function get_all(): array {
		global $wpdb;
		$select     = $this->get_select();
		$table_name = $this->get_table_name();
		// phpcs:ignore
		return $wpdb->get_results( "$select ORDER BY $table_name.id DESC LIMIT 50", ARRAY_A );
	}



	/**
	 * Get last history
	 *
	 * @return array
	 */
	public function get_last_history(): array {
		// use database.
		global $wpdb;
		$table_name = $this->get_table_name();
		$select     = $this->get_select();
		// phpcs:ignore
		$result = $wpdb->get_row( "$select ORDER BY $table_name.id DESC LIMIT 1", ARRAY_A );

		if ( ! $result ) {
			return array();
		}
		if ( $result['status_information'] ) {
			try {

				$result['status_information'] = json_decode( $result['status_information'], true );

			} catch ( \Exception $e ) {
				$result['status_information'] = array();
			}
		}
		if ( $result['error'] ) {
			try {
				$result['error'] = json_decode( $result['error'], true );
			} catch ( \Exception $e ) {
				$result['error'] = array();
			}
		}
		return $result;
	}

		/**
		 * Get last history
		 *
		 * @param int $id Environment ID.
		 * @return array
		 */
	public function get_last_completed_history_by_environment( int $id ): array {
		// use database.
		global $wpdb;
		$table_name = $this->get_table_name();

		$select = $this->get_select();
		// phpcs:ignore
		$query  = $wpdb->prepare( "$select WHERE $table_name.environment_id = %d  AND $table_name.status = %d  ORDER BY $table_name.id DESC LIMIT 1", $id, self::COMPLETED );
		// phpcs:ignore
		$result = $wpdb->get_row( $query, ARRAY_A );

		if ( ! $result ) {
			return array();
		}
		if ( $result['status_information'] ) {
			try {

				$result['status_information'] = json_decode( $result['status_information'], true );

			} catch ( \Exception $e ) {
				$result['status_information'] = array();
			}
		}
		if ( $result['error'] ) {
			try {
				$result['error'] = json_decode( $result['error'], true );
			} catch ( \Exception $e ) {
				$result['error'] = array();
			}
		}
		return $result;
	}


	/**
	 * Insert history
	 *
	 * @param array $history Environment ID.

	 * @return void
	 */
	public function insert_history( $history ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		// phpcs:ignore
		$wpdb->insert(
			$table_name,
			array(
				'environment_id' => $history['environment_id'],
				'status'         => $history['status'],
				'start_time'     => $history['start_time'],
				'end_time'       => $history['end_time'],
				'created_by'     => $history['created_by'],
			)
		);
	}

	/**
	 * Update history
	 *
	 * @param array $history History.
	 * @return void
	 */
	public function update_history( $history ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		// phpcs:ignore
		$wpdb->update(
			$table_name,
			array(
				'status'             => $history['status'],
				'end_time'           => $history['end_time'],
				'status_information' => $history['status_information'] ? wp_json_encode( $history['status_information'] ) : null,
				'error'              => $history['error'] ? wp_json_encode( $history['error'] ) : null,
			),
			array( 'id' => (int) $history['id'] )
		);
	}




	/**
	 * Add history
	 *
	 * @param string $environment_id Environment ID.
	 * @return void
	 */
	public function start_history( $environment_id ): void {
		$this->insert_history(
			array(
				'environment_id' => $environment_id,
				'status'         => self::RUNNING,
				'start_time'     => time(),
				'end_time'       => null,
				'created_by'     => get_current_user_id(),
			)
		);
	}



	/**
	 * End deployment
	 *
	 * @param int   $status Status.
	 * @param mixed $error Error.
	 * @param mixed $status_information Status information.
	 * @return void
	 */
	public function end_history( $status = self::DONE, $error = null, $status_information = null ): void {
		$last = $this->get_last_history();

		if ( empty( $last ) ) {
			return;
		}

		// if last status is not done, then update the end time.
		if ( self::DONE !== (int) $last['status'] ) {
			$last['end_time'] = time();
		}

		$last['status'] = $status;
		if ( self::FAILED === $status ) {
			$last['error'] = $error;
		}
		if ( is_array( $status_information ) ) {
			$last['status_information'] = $status_information;
		}

		$this->update_history( $last );
	}


	/**
	 * Pause deployment
	 *
	 * @param string $environment_id Environment ID.
	 * @return void
	 */
	public function pause_history( $environment_id = 0 ): void {
		$last = $this->get_last_history();

		if ( empty( $last ) ) {
			return;
		}
		if ( (int) $last['environment_id'] !== $environment_id ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				\trigger_error( 'Invalid Environment ID: ' . esc_html( $environment_id ), E_USER_WARNING );
			}
			return;
		}

		$last['status'] = self::PAUSED;

		$this->update_history( $last );
	}
}
