<?php
/**
 * Extensions Types
 *
 * @package StaticSnap
 */

namespace StaticSnap\Constants;

/**
 * This class is used to define all the filters used in the plugin
 */
abstract class Extensions_Types {
	const ALL = array(
		'search'           => Actions::REGISTER_SEARCH_EXTENSIONS,
		'environment_type' => Actions::REGISTER_ENVIRONMENT_EXTENSIONS,
		'form'             => Actions::REGISTER_FORM_EXTENSIONS,
	);
}
