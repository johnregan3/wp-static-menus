<?php
/**
 * Base Plugin class
 *
 * @todo Allow for extending cache methods (and associated classes).
 *
 * @package Mindsize\WPSM
 * @since   0.1.0
 * @author  Mindsize
 */

namespace Mindsize\WPSM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Menu_Location.
 *
 * @since 0.1.0
 */
class Menu_Location {

	/**
	 * The Plugin instance.
	 *
	 * @var stdClass
	 */
	public $plugin;

	/**
	 * Array of nav menu args passed from wp_nav_menu().
	 *
	 * @var array
	 */
	public $menu_args = [];

	/**
	 * Current theme location from wp_nav_menu().
	 *
	 * @var array
	 */
	public $location = '';

	/**
	 * Initialize.
	 *
	 * @param stdClass $args Nav menu args.
	 */
	protected function __construct( $args ) {
		$this->plugin = Plugin::get_instance();

		// Set our $menu_args property. Cast as an array for convenience when filtering.
		$this->menu_args = (array) $args;

		// Set theme location property.
		$this->location = $this->menu_args['theme_location'];
	}

	/**
	 * Check if the string is an enabled theme location.
	 *
	 * @return bool If the input location is registered for caching.
	 */
	public function is_enabled_location() {
		$is_enabled = in_array( $this->location, $this->plugin->locations, true );
		/**
		 * Filter if this theme location is enabled as for caching.
		 *
		 * @param bool   $is_enabled If the location is already enabled.
		 * @param string $location      The current theme location. This is also found in the args. Included for convenience.
		 * @param array  $args          Array of wp_nav_menu() args.
		 *
		 * @return bool If the theme location is enabled as eligble.
		 */
		return apply_filters( 'ms_wpsm_is_location_enabled', $is_enabled, $this->location, $this->menu_args );
	}

	/**
	 * Fetch the markup from the cache.
	 *
	 * @todo Incomplete.
	 *
	 * @return string The markup, else empty string.
	 */
	public function get_cached_markup() {
		$cache_method = $this->plugin->cache_method;

		if ( ! class_exists( $cache_method ) ) {
			return '';
		}

		// Fill this in.
	}

	/**
	 * Store the markup in the cache.
	 *
	 * @todo Incomplete.
	 *
	 * @param string $markup The markup to be saved.
	 */
	public function set_cached_markup( $markup ) {
		$method = $this->plugin->cache_method;

		// Fill this in.
	}
}
