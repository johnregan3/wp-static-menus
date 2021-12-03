<?php
/**
 * Cache handler
 *
 * Requires WP_Fragment_Cache to operate.
 *
 * Comments only appear in markup if WP_DEBUG is true.
 *
 * @package Mindsize\WPStaticMenus
 * @since   0.1.0
 * @author  Mindsize <info@mindsize.me>
 */

namespace Mindsize\WPStaticMenus;

/**
 * Class Cacher
 *
 * @since 0.1.0
 */
class Cacher extends \WP_Fragment_HTML_Cache {

	/**
	 * The slug of this cache class.
	 *
	 * @var string
	 */
	protected $slug = 'wp-static-menus';

	/**
	 * Get the path to the cache file.
	 *
	 * Defaults to 'wp-content/cache/wp-static-menus/{MENU LOCATION}.html'.
	 *
	 * @todo need a better default name. Could end up serving the wrong static file to the menu location.
	 *
	 * @param array $conditions Array of conditions.
	 *
	 * @return string The file path, else empty string.
	 */
	public function get_cache_file_path( $conditions ) {

		// Conditions can possibly be wrapped in an array.
		if ( is_array( $conditions ) ) {
			$conditions = array_shift( $conditions );
		}

		$file_name = ( ! empty( $conditions->theme_location ) ) ? $conditions->theme_location : 'menu';

		/**
		 * Filter the menu's cache file name.
		 *
		 * @param string   $file name  The given file name.
		 * @param stdClass $conditions The nav menu object.
		 *
		 * @return string The filtered file name.
		 */
		$filtered_file_name = apply_filters( 'wp_static_menus_cache_file_name', $file_name, $conditions );

		$file_name = ( ! empty( $filtered_file_name ) && is_string( $filtered_file_name ) ) ? $filtered_file_name : $file_name;
		$path      = $this->get_cache_path();

		return trailingslashit( $path ) . sanitize_title( $file_name ) . '.html';
	}

	/**
	 * Get the path to the directory where cache files are stored.
	 *
	 * Defaults to 'wp-content/cache/wp-static-menus/'.
	 *
	 * @param string $append Optional. String to append to the path.
	 *
	 * @return string The cache path.
	 */
	public function get_cache_path( $append = null ) {
		$path = trailingslashit( WP_CONTENT_DIR . '/cache/' . $this->slug );

		/**
		 * Filter the cache directory path.
		 *
		 * Be careful, because this can be *very* destructive!
		 *
		 * Defaults to 'wp-content/cache/wp-static-menus/'.
		 *
		 * @todo Add more params.
		 *
		 * @param string $path The default path.
		 *
		 * @return string Custom cache directory path.
		 */
		$filtered_path = apply_filters( 'wp_static_menus_cache_path', $path );

		$path = ( ! empty( $filtered_path ) && is_string( $filtered_path ) ) ? $filtered_path : $path;

		// Add any extra path string that was passed.
		$path .= ( is_string( $append ) ) ? $append : '';

		return trailingslashit( $path );
	}

	/**
	 * Return the start debug comment.
	 *
	 * @return string
	 */
	public function get_cache_start_comment() {
		if ( ! $this->is_debug() ) {
			return;
		}
		return __( 'Start WP Static Menus', 'wp-static-menus' );
	}

	/**
	 * Return the end debug comment.
	 *
	 * @return string
	 */
	public function get_cache_close_comment() {
		if ( ! $this->is_debug() ) {
			return;
		}
		return __( 'End WP Static Menus', 'wp-static-menus' );
	}
}
