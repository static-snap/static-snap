<?php
/**
 * Search templates
 *
 * @package StaticSnap
 */

namespace StaticSnap\Search;

use StaticSnap\Traits\Renderable;

/**
 * Search templates
 */
final class Search_Form_Template {


	use Renderable;

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->allowed_html = array(
			'search' => array(
				'role' => true,
			),
			'form' => array(
				'class' => true,
			),
			'input' => array(
				'id'          => true,
				'placeholder' => true,
				'type'        => true,
				'name'        => true,
				'value'       => true,
			),
			'label' => array(
				'class' => true,
				'for'   => true,
			),
			'button' => array(
				'type'       => true,
				'aria-label' => true,
			),
			'i' => array(
				'aria-hidden' => true,
				'class'       => true,
			),
			'span' => array(
				'class' => true,
			),

		);
	}
}
