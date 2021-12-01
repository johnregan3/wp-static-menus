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

	public function test_get_cache_length() {

		$cache_lengths = [
			3000,
			999999999999999999,
			0,
			false,
			'string',
			[],
			new stdClass(),
		];

		foreach ( $cache_lengths as $input ) {
			add_filter( 'wp_static_menus_cache_length', function( $cache_length ) use ( $input ) {
				return $input;
			}, 9999 );

			$this->assertTrue(
				is_numeric( $this->plugin->get_cache_length() ),
				'❗️Cache Length is not numeric: cache_length = "' . json_encode( $input ) . '"'
			);
		}
	}
}
