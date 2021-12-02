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

	const MENU_LOCATION = 'phpunitmenulocation';
	const MENU_NAME = 'phpunitmenu';

	public $plugin;
	public $class_settings;
	public $class_cacher;
	public $original_options;
	public $options;
	public $menu_id;
	public $conditions;

	protected function setUp():void {
		parent::setUp();
		$this->plugin         = ms_wp_static_menus();
		$this->class_settings = $this->plugin->settings;
		$this->class_cacher   = $this->plugin->cacher;
		$this->options        = $this->class_settings->get_option();

		// Save a copy of our option values for reset during tearDown().
		$this->original_options = $this->options;

		$this->register_menu_location();
		$this->menu_id = $this->create_nav_menu();
		$this->assign_menu_to_location();
		$this->conditions = $this->get_menu_conditions();
	}
	protected function tearDown(): void {
		$this->reset_options();
		$this->unassign_menu_from_location();
		$this->delete_nav_menu();
		parent::tearDown();
	}

	/**
	 * Change option values during testing.
	 *
	 * Note that you may need to use reset_options() to resume testing.
	 */
	protected function update_option( $key, $value ) {
		$updated_options = $this->options;
		$updated_options[ $key ] = $value;

		update_option( \Mindsize\WPStaticMenus\Settings::OPTION_NAME, $updated_options );
		$this->options = $updated_options;
	}

	/**
	 * Get a single value from our saved options.
	 */
	protected function get_option_value( $key ) {
		return ( isset( $this->options[ $key ] ) ) ? $this->options[ $key ] : false;
	}

	protected function reset_options() {
		$this->options = $this->original_options;
		update_option( \Mindsize\WPStaticMenus\Settings::OPTION_NAME, $this->original_options );
	}

	protected function register_menu_location() {
		register_nav_menus(
			[
				self::MENU_LOCATION => 'description',
			]
		);
	}

	protected function create_nav_menu() {
		$menu_id = wp_create_nav_menu( self::MENU_NAME );

		wp_update_nav_menu_item(
			$menu_id,
			0,
			[
				'menu-item-title'  =>  'Home',
				'menu-item-url'    => '/',
				'menu-item-status' => 'publish',
			]
		);

		return $menu_id;
	}

	protected function assign_menu_to_location() {
		$locations = get_theme_mod( 'nav_menu_locations' );

		if( empty( $locations ) ) {
			$locations = [];
		}

		$locations[ self::MENU_LOCATION ] = $this->menu_id;

		set_theme_mod( 'nav_menu_locations', $locations );
	}

	protected function get_menu_conditions() {
		$args = [
			'menu'                 => $this->menu_id,
			'container'            => 'div',
			'container_class'      => '',
			'container_id'         => '',
			'container_aria_label' => '',
			'menu_class'           => 'menu',
			'menu_id'              => '',
			'echo'                 => false,
			'fallback_cb'          => 'wp_page_menu',
			'before'               => '',
			'after'                => '',
			'link_before'          => '',
			'link_after'           => '',
			'items_wrap'           => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'item_spacing'         => 'preserve',
			'depth'                => 0,
			'walker'               => '',
			'theme_location'       => self::MENU_LOCATION,
		];
		return wp_nav_menu( $args );
	}

	protected function unassign_menu_from_location() {
		$locations = get_theme_mod( 'nav_menu_locations' );

		unset( $locations[ self::MENU_LOCATION ] );

		set_theme_mod( 'nav_menu_locations', $locations );
	}

	protected function delete_nav_menu() {
		wp_delete_nav_menu( self::MENU_NAME );
	}

}
