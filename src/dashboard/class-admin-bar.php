<?php
/**
 * Settings
 * This class is used to add StaticSnap menu in the admin menu in WordPress Dashboard
 *
 * @package StaticSnap
 */

namespace StaticSnap\Dashboard;

use StaticSnap\Base;
use StaticSnap\Config\Plugin;
use StaticSnap\Traits\Renderable;

/**
 * This class is used to add StaticSnap menu in the admin menu in WordPress Dashboard
 */
final class Admin_Bar extends Base {


	/**
	 * Settings page slug
	 *
	 * @var string
	 */
	const ADMIN_BAR_ID = Plugin::SLUG . '-admin-bar';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_bar_menu', array( $this, 'init' ), 999 );
	}
	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {
		global $wp_admin_bar;
		$wp_admin_bar->add_menu(
			array(
				'id'    => self::ADMIN_BAR_ID,
				'title' => '',
				'href'  => admin_url( 'admin.php?page=' . Plugin::SLUG ),
				// add menupop class to the menu item.
				'meta'  => array( 'class' => 'menupop' ),

			)
		);
	}
}
