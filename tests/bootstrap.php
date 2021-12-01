<?php
/**
 * Initialize Unit Tests
 *
 * To use this (assuming you've run `composer install` in this plugin's directory)
 * Simply use `vendor/bin/phpunit`
 *
 * Note that these tests do not use the full WP Unit Test suite.
 * It's just too much overhead for now. - JR 12/01/2021
 *
 * @link https://macarthur.me/posts/simpler-unit-testing-for-wordpress
 */

namespace WPStaticMenusTests;

require_once( __DIR__ . '/../vendor/autoload.php' );
require_once( __DIR__ . '/../../../../wp-load.php' );

class TestCase extends \PHPUnit\Framework\TestCase {
	public $plugin;
	public $class_settings;
	public $class_cacher;

	protected function setUp():void {
		parent::setUp();
		$this->plugin         = ms_wp_static_menus();
		$this->class_settings = $this->plugin->settings;
		$this->class_cacher   = $this->plugin->cacher;
	}
	protected function tearDown(): void {
		parent::tearDown();
		$this->plugin         = null;
		$this->class_settings = null;
		$this->class_cacher   = null;
	}

}
