<?php
/**
 * Search widget
 *
 * @package StaticSnap
 */

use StaticSnap\Search\Search_Form_Template;


$search_tempalte = new Search_Form_Template();

$search_tempalte->render(
	array(
		// phpcs:ignore
		'id'          => $this->get_id(),
		'label'       => esc_html__( 'Search', 'static-snap' ),
		'placeholder' => esc_html( $settings['placeholder'] ),
		'submit'      => esc_html__( 'Submit', 'static-snap' ),
	)
);

?>
<div class="static-snap-search-results">
</div>
