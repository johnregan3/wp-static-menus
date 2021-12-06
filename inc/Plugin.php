<?php
/**
 * Base Plugin class
 *
 * @package Mindsize\WPStaticMenus
 * @since   0.1.0
 * @author  Mindsize <info@mindsize.me>
 */

namespace Mindsize\WPStaticMenus;

use Mindsize\WPStaticMenus\Cacher;
use Mindsize\WPStaticMenus\Settings;

/**
 * Class Plugin.
 *
 * @since 0.1.0
 */
class Plugin {

	/**
	 * The Settings Class.
	 *
	 * @var stdClass
	 */
	public $settings;

	/**
	 * The Cacher Class.
	 *
	 * @var stdClass
	 */
	public $cacher;

	/**
	 * The enabled menu locations.
	 *
	 * @var array
	 */
	public $locations = [];

	/**
	 * Initialize.
	 *
	 * This serves as the home for all hooks.
	 *
	 * @todo add priority filter to pre_wp_nav_menu.
	 *
	 * @action plugins_loaded
	 */
	public function init() {

		if ( ! class_exists( 'WP_Fragment_Object_Cache' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_fragment_cache_not_found' ] );
			return;
		}

		$this->set_properties();
		$this->settings->init();

		add_filter( 'plugin_action_links_wp-static-menus/wp-static-menus.php', [ $this->settings, 'settings_link' ] );

		// Flush caches.
		add_action( 'wp_update_nav_menu', [ $this, 'flush_cache' ] );

		// Flush caches when saving our options.
		add_action( 'update_option_' . Settings::OPTION_NAME, [ $this, 'flush_cache' ] );

		// Go to work.
		add_filter( 'pre_wp_nav_menu', [ $this, 'pre_wp_nav_menu' ], 20, 2 );

		// Deactivation.
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

	}

	/**
	 * Set class properties.
	 */
	public function set_properties() {
		$this->settings  = Settings::get_instance();
		$this->locations = $this->get_enabled_locations();
		$this->cacher    = new Cacher();
	}

	/**
	 * Hijack wp_nav_menu() output.
	 *
	 * Check to see if cached markup exists. If so, returning the string will cause it to render.
	 * Else, call wp_nav_menu (while removing this filter), cache the markup, then return it.
	 *
	 * @filter pre_wp_nav_menu
	 *
	 * @see wp_nav_menu()
	 *
	 * @param string|null $output  The HTML content for the navigation menu.
	 * @param stdClass    $args    An object containing wp_nav_menu() arguments.
	 *
	 * @return mixed  The cached markup, else the original value of $output.
	 */
	public function pre_wp_nav_menu( $output, $args ) {
		if ( ! empty( $this->settings->get_value( 'disable_caching' ) ) ) {
			return $output;
		}

		if ( true === $this->is_excluded_user() ) {
			return $output;
		}

		if ( empty( $args->theme_location ) ) {
			return $output;
		}

		if ( true !== $this->is_enabled_location( $args ) ) {
			return $output;
		}

		remove_filter( 'pre_wp_nav_menu', [ $this, 'pre_wp_nav_menu' ], 20, 2 );

		// Must wrap $args in an array due to the way \WP_Fragment_Cache::do() is written vs. the wp_nav_menu filter.
		$markup = $this->cacher->do( 'wp_nav_menu', [ $args ], false );

		add_filter( 'pre_wp_nav_menu', [ $this, 'pre_wp_nav_menu' ], 20, 2 );

		if ( empty( $markup ) || ! is_string( $markup ) ) {
			return $output;
		}

		return $markup;
	}

	/**
	 * Fetch the array of theme menu locations to cache.
	 *
	 * @todo set via a hook or option.
	 *
	 * @return array Array of enabled Theme Locations
	 */
	public function get_enabled_locations() {
		// Fetch enabled locations from our settings.
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
		$locations = apply_filters( 'wp_static_menus_enabled_locations', $locations );

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
	 * @return bool If the theme location is registered for caching.
	 */
	public function is_enabled_location( $menu_args ) {
		if ( empty( $menu_args->theme_location ) ) {
			return false;
		}

		$is_enabled = in_array( $menu_args->theme_location, $this->locations, true );

		/**
		 * Filter if this theme location is enabled for caching.
		 *
		 * @param bool   $is_enabled  If the location is already enabled.
		 * @param string $location    The current theme location. This is also found in the args. Included for convenience.
		 * @param object $args        Object of wp_nav_menu() args.
		 *
		 * @return bool If the theme location has caching enabled.
		 */
		return apply_filters( 'wp_static_menus_is_location_enabled', $is_enabled, $menu_args->theme_location, $menu_args );
	}

	/**
	 * If the current user should be shown the cached menu.
	 *
	 * These are set by roles (not capabilities) to simplify the
	 * settings for the user.
	 *
	 * @todo Add filter for admin roles.
	 *
	 * @return bool If the cached menu should be shown.
	 */
	public function is_excluded_user() {
		$user       = wp_get_current_user();
		$exceptions = $this->settings->get_value( 'exceptions' );

		if ( empty( $exceptions ) || '0' === $exceptions ) {
			return false;
		}

		if ( 'logged_in' === $exceptions && is_user_logged_in() ) {
			return true;
		}

		$user_roles = $user->roles;

		if ( 'admins' === $exceptions ) {

			$admin_roles = [
				'administrator',
				'editor',
			];

			foreach ( $admin_roles as $role ) {
				if ( in_array( $role, $user_roles, true ) ) {
					return true;
				}
			}

			if ( is_super_admin( $user->ID ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Filter the Cache Length Setting.
	 *
	 * @return int|string Cache length, in minutes.
	 */
	public function get_cache_length() {

		// The value from settings is set in minutes for simplicity.
		$cache_length = $this->settings->get_value( 'cache_length' );

		// Defaults to 60 min if setting is empty.
		if ( empty( $cache_length ) || ! is_numeric( $cache_length ) ) {
			$cache_length = Cacher::DEFAULT_CACHE_LENGTH;
		}

		/**
		 * Override the caching length from the plugin settings.
		 *
		 * @todo add $menu_args to this filter.
		 *
		 * @param string $method The cache length.
		 *
		 * @return string The desired cache length, in minutes.
		 */
		$filtered_cache_length = apply_filters( 'wp_static_menus_cache_length', $cache_length );

		if ( ! empty( $filtered_cache_length ) && is_numeric( $filtered_cache_length ) ) {
			return $filtered_cache_length;
		}

		return $cache_length;
	}

	/**
	 * If the cache time has expired, flush the cache.
	 *
	 * Checks to see if the cache transient still exists.
	 * If not, then delete the cached files.
	 */
	public function maybe_flush_cache() {
		if ( $this->is_time_to_flush_cache() ) {
			$this->flush_cache();
		}
	}

	/**
	 * Check the transient to see if it is time to flush the cache.
	 *
	 * If our transient is empty, cache time has expired.
	 *
	 * @return bool
	 */
	public function is_time_to_flush_cache() {
		return empty( get_transient( Cacher::TRANSIENT_NAME ) );
	}

	/**
	 * Empty the Menu Cache.
	 *
	 * @action wp_update_nav_menu
	 * @action update_option_
	 */
	public function flush_cache() {
		$this->cacher->clear_cache();
		$this->reset_cache_timer();
	}

	/**
	 * Reset the "timer" transient for the cache.
	 */
	public function reset_cache_timer() {
		$cache_length = $this->get_cache_length();

		// If anything's wrong, use the default length.
		if ( empty( $cache_length ) || ! is_numeric( $cache_length ) ) {
			$cache_length = Cacher::DEFAULT_CACHE_LENGTH;
		}

		set_transient( Cacher::TRANSIENT_NAME, 1, $cache_length );
	}

	/**
	 * Render Admin Notice if WP_Fragment_Cache plugin not found.
	 *
	 * @action admin_notices
	 */
	public function admin_notice_fragment_cache_not_found() {
		// Only load on Plugins admin screen.
		if ( 'plugins' !== get_current_screen()->id ) {
			return;
		}
		?>
		<div class="error notice">
			<p><?php esc_html_e( 'WP Fragment Cache Plugin is required for Menu Cache to operate.', 'wp-static-menus' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Clean up on deactivation.
	 *
	 * @todo remove 'wp-content/wp-static-menus' directory itself.
	 */
	public function deactivate() {
		$this->flush_cache();
	}
}
