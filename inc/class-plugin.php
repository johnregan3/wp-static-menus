<?php
/**
 * Base Plugin class
 *
 * @package Mindsize\WPSM
 * @since   0.1.0
 * @author  Mindsize
 */

namespace Mindsize\WPSM;

/**
 * Class Plugin.
 *
 * @since 0.1.0
 */
class Plugin extends Singleton {

	/**
	 * Lowercase class name.
	 */
	const CACHE_METHOD_OBJECT_CACHE = 'Cache_Object';

	/**
	 * Lowercase class name.
	 */
	const CACHE_METHOD_HTML = 'Cache_HTML';

	/**
	 * Lowercase class name.
	 */
	const CACHE_METHOD_TRANSIENT = 'Cache_Transient';

	/**
	 * Array of caching methods.
	 *
	 * @var array
	 */
	protected $cache_methods = [
		self::CACHE_METHOD_OBJECT_CACHE,
		self::CACHE_METHOD_HTML,
		self::CACHE_METHOD_TRANSIENT,
	];

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
	 * This serves as the home for all hooks.
	 *
	 * @action plugins_loaded
	 */
	protected function init() {

		// On "get menu"...
			// See if it is registered to this plugin
				// Registered via location, not wp menu name.
			// Get cached version
				// Determine method
			// Render it.
			// Cache it.
				// Determine method.

		add_filter( 'pre_nav_menu', [ $this, 'pre_nav_menu' ], 20, 2 );
	}

	/**
	 * Pre Nav Filter hook.
	 *
	 * This hook allows us to hijack the output when wp_nav_menu() is calld.
	 *
	 * @filter pre_nav_menu
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string|null $output Nav menu output to short-circuit with. Default null.
	 * @param stdClass    $args   An object containing wp_nav_menu() arguments.
	 *
	 * @return mixed  The cached markup, else the original value of $output.
	 */
	protected function pre_nav_menu( $output, $args ) {
		if ( empty( $args['theme_location'] ) ) {
			return $output;
		}

		// Set our $menu_args property.
		$this->menu_args = (array) $args;

		// Set theme location property.
		$this->location = $this->menu_args['theme_location'];

		$is_registered = $this->is_registered_location();

		/**
		 * Filter if this theme location is registered as for caching.
		 *
		 * @param bool   $is_registered If the location is already registered.
		 * @param string $location      The current theme location. This is also found in the args. Included for convenience.
		 * @param array  $args          Array of wp_nav_menu() args.
		 *
		 * @return bool If the theme location is registered as eligble.
		 */
		$is_registered = apply_filters( 'ms_wpsm_is_location_registered', $is_registered, $this->location, $this->menu_args );

		if ( true !== $is_registered ) {
			return $output;
		}

		$markup = $this->get_cached_markup();

		if ( ! empty( $markup ) && is_string( $markup ) ) {
			return $markup;
		}

		return $output;
	}

	/**
	 * Check if the string is a user-designated theme location.
	 *
	 * @return bool If the input location is registered with the plugin.
	 */
	public function is_registered_location() {
		return in_array( $this->location, $this->registered_locations(), true );
	}

	/**
	 * Fetch the array of user-designated theme menu locations to cache.
	 *
	 * @todo set via a hook or option.
	 *
	 * @return array Array of registered Theme Locations
	 */
	public function get_registered_locations() {
		// Fetch user-designated theme locations to cache.
		$locations = [];

		/**
		 * Filters theme locations eligible for caching.
		 *
		 * @param array $locations Array of saved locations.
		 *
		 * @return array Array of eligible locations.
		 */
		$locations = appy_filters( 'ms_wpsm_registered_locations', $locations );
		if ( ! is_array( $locations ) ) {
			$locations = [];
		}
		return $locations;
	}

	/**
	 * Fetch the markup from the cache.
	 *
	 * @return string The markup, else empty string.
	 */
	public function get_cached_markup() {
		$cache_method = $this->get_cache_method();

		if ( ! class_exists( $cache_method ) ) {
			return '';
		}
	}

	/**
	 * Get the user-desginated caching method.
	 *
	 * @todo set via hook or option.
	 *
	 * @return string The caching method.
	 */
	public function get_cache_method() {

		$method = self::CACHE_METHOD_TRANSIENT;

		/**
		 * The desired caching method.
		 *
		 * Requires use of one of this plugin's caching methods.  See this Class's constants.
		 *
		 * @param string $method The caching method.
		 * @param array  $args   The nav menu args.
		 *
		 * @return string The desired caching methods.
		 */
		$filtered_method = apply_filters( 'ms_wpsm_cache_method', $method, $this->menu_args );

		return in_array( $filtered_method, $this->cache_methods, true ) ? $filtered_methods : $method;
	}
}
