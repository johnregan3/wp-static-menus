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

	public function test_get_cache_file_path() {

		$file_names = [
			3000,
			999999999999999999,
			0,
			false,
			'string',
			[],
			new stdClass(),
		];

		foreach ( $file_names as $name ) {
			add_filter( 'wp_static_menus_cache_file_name', function( $file_name, $conditions ) use ( $name ) {
				return $name;
			}, 9999, 2 );

			$tested_file_path = $this->class_cacher->get_cache_file_path( (array) $this->conditions );

			$this->assertTrue(
				is_string( $tested_file_path ),
				'❗️Cache file path/name is not a string: file_name = "' . json_encode( $name ) . '"'
			);

			$this->assertTrue(
				( false !== strpos( $tested_file_path, '.html' ) ),
				'❗️Cache file name does not contain \'.html\': file_name = "' . json_encode( $name ) . '"'
			);
		}
	}

	public function test_get_cache_path() {
		$test_paths = [
			3000,
			999999999999999999,
			0,
			false,
			'string',
			'string/string1/string2/',
			[],
			new stdClass(),
		];

		foreach ( $test_paths as $test_path ) {
			add_filter( 'wp_static_menus_cache_path', function( $path ) use ( $test_path ) {
				return $test_path;
			}, 9999 );

			$tested_cache_path = $this->class_cacher->get_cache_path();

			$this->assertTrue(
				is_string( $tested_cache_path ),
				'❗️Cache Path is not a string: test_path = "' . json_encode( $test_path ) . '"'
			);
		}
	}
}
