<?php
/**
 * Forms Task
 *
 * @package StaticSnap
 */

namespace StaticSnap\Deployment\Deploy;

use StaticSnap\Application;
use StaticSnap\Config\Options;
use StaticSnap\Connect\Connect;
use StaticSnap\Deployment\Task;


/**
 * Forms Task class
 */
final class Forms_Task extends Task {
	/**
	 * Task name
	 *
	 * @var string
	 */
	protected $description = 'Syncing Forms';


	/**
	 * Perform task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @return bool
	 */
	public function perform(): bool {

		$forms_options = Options::instance()->get(
			'forms',
			array(
				'enabled' => false,
			)
		);

		if ( ! $forms_options['enabled'] ) {
			return true;
		}

		$connect_data = Connect::instance()->get_connect_data();
		if ( empty( $connect_data ) || empty( $connect_data['installation_access_token'] ) ) {
			return true;
		}

		$extensions = Application::instance()->get_extensions_by_type( 'form' );
		try {
			foreach ( $extensions as $extension ) {
				// just in case it fails with an exception, by default we set it to false.
				$extension->sync_forms_settings();
			}
		} catch ( \Exception $_e ) {
			// Continue anyway.
			return true;
		}

		return true;
	}
}
