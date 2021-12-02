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

	/**
	 * Expected outcome should be:
	 * 1. No slashes at the start.
	 * 2. One slash at the end.
	 * 3. No more than one slash at time in between.
	 */
	public function test_sanitize_settings() {

		// Test no double-slash at start that wp_normalize_path typically allows.
		$input = [
			'cache_path' => '//cache/',
		];

		$output = $this->class_settings->sanitize_settings( $input );

		$this->assertEquals(
			$output['cache_path'],
			'cache/',
			'❗️Failed "No double-slash at start of Path'
		);

		// Test no double-slash in path.
		$input = [
			'cache_path' => 'cache///subdir/',
		];

		$output = $this->class_settings->sanitize_settings( $input );

		$this->assertEquals(
			$output['cache_path'],
			'cache/subdir/',
			'❗️Failed "No double-slash in Path'
		);

		// Test no double-slash at ending.
		$input = [
			'cache_path' => 'cache/subdir/////',
		];

		$output = $this->class_settings->sanitize_settings( $input );

		$this->assertEquals(
			$output['cache_path'],
			'cache/subdir/',
			'❗️Failed "No double-slash at end of Path"'
		 );

		// Test ensure one slash at ending.
		$input = [
			'cache_path' => 'cache/subdir',
		];

		$output = $this->class_settings->sanitize_settings( $input );

		$this->assertEquals(
			$output['cache_path'],
			'cache/subdir/',
			'❗️Failed "Only one slash at end of Path"'
		);

		// Test ensure multiple directories allowed.
		$input = [
			'cache_path' => 'cache/subdir/sub-subdir/sub-sub-subdir/',
		];

		$output = $this->class_settings->sanitize_settings( $input );

		$this->assertEquals(
			$output['cache_path'],
			'cache/subdir/sub-subdir/sub-sub-subdir/',
			'❗️Failed "Ensure multiple directories allowed"'
		);
	}
}
