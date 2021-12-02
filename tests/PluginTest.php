<?php
/**
 * Test Class Mindsize\WPStaticMenus\Plugin
 */
class PluginTest extends WPStaticMenusTests\TestCase {

	public function test_PluginIsActivated() {
		$this->assertTrue(
			class_exists( 'Mindsize\WPStaticMenus\Plugin' ),
			'❗️WP Static Menus Plugin is not activated.'
		);

		$this->assertTrue(
			class_exists( 'WP_Fragment_Object_Cache' ),
			'❗️WP Fragment Cache Plugin is not activated.'
		);

		$this->assertTrue(
			is_a( $this->plugin, 'Mindsize\WPStaticMenus\Plugin' ),
			'❗️Plugin instance is not loaded in Unit Tests.'
		);

	}

	public function test_init() {

		$this->assertTrue(
			is_a( $this->class_settings, 'Mindsize\WPStaticMenus\Settings' ),
			'❗️Plugin property $settings is not instantiated'
		);

		$this->assertTrue(
			is_a( $this->class_cacher, 'Mindsize\WPStaticMenus\Cacher' ),
			'❗️Plugin property $cacher is not instantiated'
		);

		$this->assertTrue(
			is_array( $this->plugin->locations ),
			'❗️Plugin property $locations is not an array'
		);
	}
}
