<?php
/**
 * Extensions Bootstrap File
 *
 * @package StaticSnap
 */

namespace StaticSnap\Extensions;

use StaticSnap\Constants\Actions;
use StaticSnap\Extensions\Elementor\Elementor_Extension;
use StaticSnap\Extensions\Search\FuseJS\Fuse_JS_Search_Extension;
use StaticSnap\Extensions\Search\Algolia\Algolia_Extension;
use StaticSnap\Extensions\Forms\Elementor\Elementor_Form_Extension;
use StaticSnap\Extensions\Forms\Contact_Form_7\Contact_Form_7_Extension;
use StaticSnap\Extensions\Forms\Gravity_Forms\Gravity_Forms_Extension;
use StaticSnap\Extensions\Forms\WP_Forms\WP_Forms_Extension;
use StaticSnap\Extensions\InstaWP\InstaWP_Extension;
use StaticSnap\Extensions\TranslatePress\TranslatePress_Extension;
use StaticSnap\Extensions\WP_Rocket\WP_Rocket_Extension;
use StaticSnap\Extensions\WPML\WPML_Extension;

$extensions = array(
	Elementor_Extension::class,
	TranslatePress_Extension::class,
	WPML_Extension::class,
	InstaWP_Extension::class,
	Fuse_JS_Search_Extension::class,
	Algolia_Extension::class,
	Elementor_Form_Extension::class,
	Contact_Form_7_Extension::class,
	WP_Forms_Extension::class,
	Gravity_Forms_Extension::class,
	WP_Rocket_Extension::class,

);



add_action(
	Actions::DEPLOYMENT_PROCESS_INIT_EXTENSIONS,
	function () use ( $extensions ) {

		foreach ( $extensions as $extension ) {
			new $extension();
		}
	},
);
