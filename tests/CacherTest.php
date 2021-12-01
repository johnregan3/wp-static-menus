<?php
/**
 * Test Class Mindsize\WPStaticMenus\Cacher
 */

class CacherTest extends WPStaticMenusTests\TestCase {

	public function test_settings_init() {
		$this->assertTrue(
			is_a( $this->class_cacher, 'Mindsize\WPStaticMenus\Cacher' ),
			'❗️Class Cacher is not instantiated by \Plugin'
		);
	}
}
