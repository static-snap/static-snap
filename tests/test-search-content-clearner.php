<?php

namespace StaticSnap\Tests;

use StaticSnap\Search\Content_Cleaner;


/**
 * Test class for Content_Cleaner.
 */

class Test_Content_Cleaner extends \WP_UnitTestCase {

	public function test_remove_content_noise() {
		$content = '<style>
		body{
			background-color: #fff;
		}
		</style>
		<code>code here</code><div>Test content</div><p>test<pre>Pre content</pre></p>
		<!--
		comment
		comment 2
		-->';

		$cleaned_content = Content_Cleaner::remove_content_noise( $content );


		$this->assertEquals( '<div>Test content</div><p>test</p>', $cleaned_content );


	}

	public function test_remove_content_noise_encoding() {
		$content = '<div>Día año</div>';

		$cleaned_content = Content_Cleaner::remove_content_noise( $content );

		$this->assertEquals( '<div>Día año</div>', $cleaned_content );

	}


}
