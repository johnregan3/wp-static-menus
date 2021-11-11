<?php
/**
 * Base Plugin class
 *
 * @todo Allow for extending cache methods (and associated classes).
 * @todo Delete caches on plugin deactivation.
 * @todo Delete caches when settings are updated.
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
	 * The user-designated caching method.
	 *
	 * @var array
	 */
	public $cache_method = '';

	/**
	 * The enabled menu locations.
	 *
	 * @var array
	 */
	public $locations = [];

	/**
	 * The length of time to save the cache.
	 *
	 * WP Time constants (e.g., HOUR_IN_SECONDS) work here.
	 *
	 * @var int Lenghth of time, in seconds.
	 */
	public $cache_length = MONTH_IN_SECONDS;

	/**
	 * Initialize.
	 *
	 * This serves as the home for all hooks.
	 *
	 * // Clear cache on update menus.
	 *
	 * @action plugins_loaded
	 */
	protected function init() {
		add_filter( 'pre_nav_menu', [ $this, 'pre_nav_menu' ], 20, 2 );
	}

	/**
	 * Set properties.
	 */
	protected function set_properties() {
		$this->set_properties( $args );
		$this->cache_method = $this->get_cache_method();
		$this->locations    = $this->get_enabled_locations();
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
		// Sanity check.
		if ( empty( $args->theme_location ) ) {
			return $output;
		}

		$menu = new Menu_Location( $args );

		if ( true !== $menu->is_enabled_location() ) {
			return $output;
		}

		$markup = $this->get_cached_markup();

		if ( ! empty( $markup ) && is_string( $markup ) ) {
			$this->set_cached_markup( $markup );
			return $markup;
		}

		return $output;
	}

	/**
	 * Fetch the array of theme menu locations to cache.
	 *
	 * @todo set via a hook or option.
	 *
	 * @return array Array of enabled Theme Locations
	 */
	public function get_enabled_locations() {
		// Fetch user-designated theme locations to cache.
		$locations = [];

		/**
		 * Filters theme locations eligible for caching.
		 *
		 * @param array $locations Array of saved locations.
		 *
		 * @return array Array of eligible locations.
		 */
		$locations = appy_filters( 'ms_wpsm_enabled_locations', $locations );
		if ( ! is_array( $locations ) ) {
			$locations = [];
		}
		return $locations;
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
		 * Requires use of one of this plugin's caching methods. See this Class's constants.
		 *
		 * @param string $method The caching method.
		 *
		 * @return string The desired caching method.
		 */
		$filtered_method = apply_filters( 'ms_wpsm_cache_method', $method );

		return in_array( $filtered_method, $this->cache_methods, true ) ? $filtered_methods : $method;
	}
}
