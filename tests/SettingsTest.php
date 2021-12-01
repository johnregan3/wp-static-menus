<?php
/**
 * Test Class Mindsize\WPStaticMenus\Settings
 */

class SettingsTest extends WPStaticMenusTests\TestCase {

	public function test_settings_init() {
		$this->assertTrue(
			is_a( $this->class_settings, 'Mindsize\WPStaticMenus\Settings' ),
			'❗️Class Settings is not instantiated by \Plugin'
		);
	}
}
