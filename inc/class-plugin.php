<?php
/**
 * Base Plugin class
 *
 * @todo Delete caches on plugin deactivation.
 * @todo Delete caches when settings are updated.
 * @todo Delete caches when menus are edited.
 * @todo Delete caches when theme is saved...?
 *
 * @todo Settings: Build Delete all Caches
 * @todo allow filter for cache length
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
	const CACHE_METHOD_OBJECT_CACHE = 'Mindsize\WPSM\Cache_Object';

	/**
	 * Lowercase class name.
	 */
	const CACHE_METHOD_HTML = 'Mindsize\WPSM\Cache_HTML';

	/**
	 * Lowercase class name.
	 */
	const CACHE_METHOD_TRANSIENT = 'Mindsize\WPSM\Cache_Transient';

	/**
	 * The WP Admin Settings instance.
	 *
	 * @var object
	 */
	public $settings;

	/**
	 * Array of caching methods.
	 *
	 * @var array
	 */
	public $cache_methods = [];

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
	 * @todo create a filter for this.
	 *
	 * @var int Length of time, in seconds.
	 */
	public $cache_length = MONTH_IN_SECONDS;

	/**
	 * Initialize.
	 *
	 * This serves as the home for all hooks.
	 *
	 * @action plugins_loaded
	 */
	public function init() {
		$this->set_properties();

		if ( ! empty( $this->settings->get_value( 'disable_caching' ) ) ) {
			return;
		}

		add_filter( 'pre_wp_nav_menu', [ $this, 'pre_wp_nav_menu' ], 20, 2 );
		add_filter( 'wp_nav_menu', [ $this, 'wp_nav_menu' ], 20, 2 );
	}

	/**
	 * Set class properties.
	 */
	public function set_properties() {
		$this->settings      = Settings::get_instance();
		$this->cache_methods = $this->get_cache_methods();
		$this->cache_method  = $this->get_cache_method();
		$this->cache_length  = $this->get_cache_length();
		$this->locations     = $this->get_enabled_locations();
	}

	/**
	 * Pre Nav Filter hook.
	 *
	 * This hook allows us to hijack the output when wp_nav_menu() is called.
	 *
	 * Check to see if cached markup exists.  If not, let wp_nav_menu() proceed
	 * and we'll cache the markup as wp_nav_menu() ends.
	 *
	 * @filter pre_wp_nav_menu
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string|null $output The HTML content for the navigation menu.
	 * @param stdClass    $args     An object containing wp_nav_menu() arguments.
	 *
	 * @return mixed  The cached markup, else the original value of $output.
	 */
	public function pre_wp_nav_menu( $output, $args ) {
		// Sanity check.
		if ( empty( $args->theme_location ) ) {
			return $output;
		}

		if ( true !== $this->is_enabled_location( $args ) ) {
			return $output;
		}

		$cache  = $this->get_cache_object( $args );
		$markup = $cache->get_markup( $args );

		if ( empty( $markup ) || ! is_string( $markup ) ) {
			return $markup;
		}

		return $output;
	}

	/**
	 * WP Nav Filter hook.
	 *
	 * This hook fires at the last moment before the nav menu is rendered.
	 *
	 * We use this to cache the markup so that the next time pre_wp_nav_menu is called,
	 * the markup is already available.
	 *
	 * @filter wp_nav_menu
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string   $nav_menu The HTML content for the navigation menu.
	 * @param stdClass $args     An object containing wp_nav_menu() arguments.
	 *
	 * @return mixed  The markup.
	 */
	public function wp_nav_menu( $nav_menu, $args ) {
		if ( empty( $args->theme_location ) ) {
			return $nav_menu;
		}

		if ( true !== $this->is_enabled_location( $args ) ) {
			return $nav_menu;
		}

		$cache = $this->get_cache_object( $args );
		$cache->set_markup( $nav_menu );

		return $nav_menu;
	}

	/**
	 * Fetch the array of theme menu locations to cache.
	 *
	 * @todo set via a hook or option.
	 *
	 * @return array Array of enabled Theme Locations
	 */
	public function get_enabled_locations() {
		// Fetch user-designated theme locations.
		$setting = $this->settings->get_value( 'theme_locations' );

		if ( empty( $setting ) || ! is_array( $setting ) ) {
			$setting = [];
		}

		// Use array_keys due to the way the setting is saved.
		$locations = array_keys( $setting );

		/**
		 * Filters theme locations eligible for caching.
		 *
		 * @param array $locations Array of saved locations.
		 *
		 * @return array Array of eligible locations.
		 */
		$locations = apply_filters( 'ms_wpsm_enabled_locations', $locations );

		if ( ! is_array( $locations ) ) {
			$locations = [];
		}

		return $locations;
	}

	/**
	 * Check if these menu args request a valid theme location.
	 *
	 * @param object $menu_args WP Nav Menu args.
	 *
	 * @return bool If the input location is registered for caching.
	 */
	public function is_enabled_location( $menu_args ) {
		if ( empty( $menu_args->theme_location ) ) {
			return false;
		}

		$is_enabled = in_array( $menu_args->theme_location, $this->locations, true );

		/**
		 * Filter if this theme location is enabled as for caching.
		 *
		 * @param bool   $is_enabled If the location is already enabled.
		 * @param string $location      The current theme location. This is also found in the args. Included for convenience.
		 * @param object $args          Object of wp_nav_menu() args.
		 *
		 * @return bool If the theme location is enabled as eligble.
		 */
		return apply_filters( 'ms_wpsm_is_location_enabled', $is_enabled, $location, $menu_args );
	}

	/**
	 * Get all registered Caching methods.
	 *
	 * Used for allowing user choice in Plugin settings.
	 *
	 * Note that each class that extends Cache class must set a display_name property.
	 *
	 * @return array Array of allowed methods.
	 */
	public function get_cache_methods() {
		$output        = [];
		$cache_methods = [
			self::CACHE_METHOD_OBJECT_CACHE,
			self::CACHE_METHOD_HTML,
			self::CACHE_METHOD_TRANSIENT,
		];

		/**
		 * Note that Class names must be fully-qualified with all relevant namespaces.
		 *
		 * @param array $cache_methods Default Cache methods.
		 *
		 * @return array
		 */
		$cache_methods = apply_filters( 'ms_wpsm_cache_methods', $cache_methods );

		// Ensure each class exists and has set the proper display_name property.
		foreach ( $cache_methods as $class_name ) {
			if ( class_exists( $class_name ) && is_subclass_of( $class_name, 'Mindsize\WPSM\Cache' ) ) {

				$class = new $class_name( [] );
				if ( ! is_wp_error( $class->display_name ) && ! empty( $class->display_name ) ) {

					// Must have a display_name property set to be included.
					$output[ $class->display_name ] = $class_name;
				}
			}
		}
		return $output;
	}

	/**
	 * Get the user-desginated caching method.
	 *
	 * @todo set via hook or option.
	 *
	 * @return string The caching method.
	 */
	public function get_cache_method() {

		$settings_method = $this->settings->get_value( 'caching_method' );

		/**
		 * Override the caching method from the plugin settings.
		 *
		 * Requires use of one of this plugin's registered caching methods.
		 *
		 * @todo add $menu_args to this filter.
		 *
		 * @see ms_wpsm_cache_methods filter
		 *
		 * @param string $method The caching method.
		 *
		 * @return string The desired caching method.
		 */
		$filtered_method = apply_filters( 'ms_wpsm_cache_method', $settings_method );

		return in_array( $filtered_method, array_values( $this->cache_methods ), true ) ? $filtered_method : $settings_method;
	}

	/**
	 * Filter the Cache Length Setting.
	 *
	 * @return int|string Cache length, in seconds.
	 */
	public function get_cache_length() {
		$cache_length = $this->settings->get_value( 'cache_length' );

		/**
		 * Override the caching length from the plugin settings.
		 *
		 * @todo add $menu_args to this filter.
		 *
		 * @param string $method The cache length.
		 *
		 * @return string The desired cache length, in seconds.
		 */
		$filtered_cache_length = apply_filters( 'ms_wpsm_cache_length', $cache_length );

		if ( ! empty( $filtered_cache_length ) && is_numeric( $filtered_cache_length ) ) {
			return $filtered_cache_length;
		}

		return $cache_length;
	}

	/**
	 * Get a new instance of the Cache class.
	 *
	 * @param array $menu_args Array of WP Nav Menu args.
	 *
	 * @return bool|stdClass An instance of the Cache class.
	 */
	public function get_cache_object( $menu_args ) {
		$cache_class = $this->get_cache_method();

		if ( class_exists( $cache_class ) && is_subclass_of( $cache_class, 'Mindsize\WPSM\Cache' ) ) {
			return new $cache_class( $menu_args );
		}

		return false;
	}
}
